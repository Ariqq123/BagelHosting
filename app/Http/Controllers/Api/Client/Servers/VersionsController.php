<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\JsonResponse;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Http\Requests\Api\Client\Servers\Versions\InstallVersionRequest;
use Pterodactyl\Http\Requests\Api\Client\Servers\Versions\ViewVersionsRequest;
use Pterodactyl\Models\Egg;
use Pterodactyl\Models\Nest;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\ServerVariable;
use Pterodactyl\Services\Minecraft\McVersionsCatalogService;
use Pterodactyl\Services\Servers\ReinstallServerService;

class VersionsController extends ClientApiController
{
    public function __construct(
        private ConnectionInterface $connection,
        private ReinstallServerService $reinstallServerService,
    ) {
        parent::__construct();
    }

    public function index(Server $server, ViewVersionsRequest $request): JsonResponse
    {
        $eggs = $this->managedEggs()->get(['id', 'name', 'description']);
        $mcJarsTypes = $this->mcJarsTypes();
        $currentEgg = $server->egg;
        $versionVariable = $server->variables()
            ->where('env_variable', 'MINECRAFT_VERSION')
            ->first();

        return new JsonResponse([
            'software' => $eggs->map(function (Egg $egg) use ($mcJarsTypes) {
                $type = $this->mcJarsTypeForEgg($egg);
                $metadata = $mcJarsTypes[$type] ?? [];

                return [
                    'id' => $egg->id,
                    'name' => $egg->name,
                    'type' => $type,
                    'description' => trim(str_replace(McVersionsCatalogService::MARKER, '', $egg->description ?? '')),
                    'icon' => $metadata['icon'] ?? null,
                    'color' => $metadata['color'] ?? null,
                    'versions' => $this->mcJarsVersionsForType($type),
                ];
            })->values(),
            'current' => [
                'egg_id' => $currentEgg?->id,
                'name' => $currentEgg?->name,
                'version' => $versionVariable?->server_value ?? $versionVariable?->default_value ?? 'latest',
            ],
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function store(Server $server, InstallVersionRequest $request): JsonResponse
    {
        $version = $request->input('version');
        $egg = $this->managedEggs()->where('id', $request->integer('egg_id'))->first();

        if (!$egg) {
            throw new DisplayException('The selected software is not a valid Minecraft Versions option.');
        }

        $variable = $egg->variables()->where('env_variable', 'MINECRAFT_VERSION')->first();

        if (!$variable) {
            throw new DisplayException('The selected software does not support Minecraft version selection.');
        }

        $this->connection->transaction(function () use ($server, $egg, $variable, $version) {
            ServerVariable::query()->updateOrCreate(
                [
                    'server_id' => $server->id,
                    'variable_id' => $variable->id,
                ],
                ['variable_value' => $version]
            );

            $server->forceFill([
                'egg_id' => $egg->id,
                'nest_id' => $egg->nest_id,
                'startup' => $egg->startup,
                'image' => $this->defaultDockerImage($egg),
            ])->save();
        });

        $this->reinstallServerService->handle($server->fresh());

        Activity::event('server:versions.change')
            ->property('egg', $egg->name)
            ->property('version', $version)
            ->log();

        return new JsonResponse(['success' => true]);
    }

    private function managedEggs()
    {
        return Egg::query()
            ->where('author', McVersionsCatalogService::AUTHOR)
            ->whereHas('nest', fn ($query) => $query->where('name', McVersionsCatalogService::NEST_NAME))
            ->orderBy('name');
    }

    private function defaultDockerImage(Egg $egg): string
    {
        $images = $egg->docker_images ?? [];

        return (string) (reset($images) ?: $egg->docker_image);
    }

    private function mcJarsTypes(): array
    {
        return Cache::remember('mc_versions.mcjars_types', now()->addDay(), function () {
            try {
                $response = Http::timeout(5)->get('https://mcjars.app/api/v1/types');

                if (!$response->successful()) {
                    return [];
                }

                return $response->json('types') ?? [];
            } catch (\Throwable) {
                return [];
            }
        });
    }

    private function mcJarsVersionsForType(string $type): array
    {
        return Cache::remember("mc_versions.mcjars_versions.v2.{$type}", now()->addHour(), function () use ($type) {
            try {
                $response = Http::timeout(8)->get("https://mcjars.app/api/v1/builds/{$type}");

                if (!$response->successful()) {
                    return [];
                }

                $versions = [];
                foreach (($response->json('versions') ?? []) as $version => $metadata) {
                    if (!is_string($version) || !preg_match('/^[A-Za-z0-9._+-]{1,32}$/', $version)) {
                        continue;
                    }

                    $versions[] = [
                        'id' => $version,
                        'type' => strtoupper((string) ($metadata['type'] ?? 'RELEASE')),
                        'created' => strtotime((string) ($metadata['created'] ?? '')) ?: 0,
                    ];
                }

                usort($versions, fn ($a, $b) => $b['created'] <=> $a['created'] ?: version_compare($b['id'], $a['id']));

                return array_map(
                    fn ($version) => ['id' => $version['id'], 'type' => $version['type']],
                    array_slice($versions, 0, 250)
                );
            } catch (\Throwable) {
                return [];
            }
        });
    }

    private function mcJarsTypeForEgg(Egg $egg): string
    {
        return McVersionsCatalogService::typeForName($egg->name);
    }
}

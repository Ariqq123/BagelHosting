<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Models\ArixPlugin;
use Pterodactyl\Models\Server;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;
use Pterodactyl\Services\Plugins\PluginMarketplaceService;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Http\Requests\Api\Client\Servers\Plugins\ListPluginsRequest;
use Pterodactyl\Http\Requests\Api\Client\Servers\Plugins\DownloadPluginRequest;
use Pterodactyl\Http\Requests\Api\Client\Servers\Plugins\MarketplacePluginRequest;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PluginController extends ClientApiController
{
    public function __construct(private DaemonFileRepository $fileRepository, private PluginMarketplaceService $marketplace)
    {
        parent::__construct();
    }

    public function index(ListPluginsRequest $request, Server $server): array
    {
        return [
            'object' => 'list',
            'data' => ArixPlugin::query()
                ->enabled()
                ->orderBy('name')
                ->get()
                ->map(fn (ArixPlugin $plugin) => $this->transform($plugin))
                ->values(),
        ];
    }

    public function download(DownloadPluginRequest $request, Server $server, ArixPlugin $plugin): JsonResponse
    {
        if (!$plugin->enabled) {
            throw new BadRequestHttpException('This plugin is disabled.');
        }

        $this->pullToPlugins($server, $plugin->download_url, $plugin->filename);

        Activity::event('server:plugin.download')
            ->property('plugin_id', $plugin->id)
            ->property('plugin_name', $plugin->name)
            ->property('filename', $plugin->filename)
            ->log();

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    public function searchMarketplace(MarketplacePluginRequest $request, Server $server): array
    {
        $platform = (string) $request->query('platform', 'modrinth');
        $query = (string) $request->query('query', 'minecraft');
        $page = (int) $request->query('page', 1);
        $version = (string) $request->query('version', '');
        $loader = (string) $request->query('loader', '');

        return $this->marketplace->search($platform, $query, $page, $version, $loader);
    }

    public function marketplaceVersions(MarketplacePluginRequest $request, Server $server, string $platform, string $project): array
    {
        return [
            'data' => $this->marketplace->versions(
                $platform,
                $project,
                (string) $request->query('version', ''),
                (string) $request->query('loader', '')
            ),
        ];
    }

    public function installMarketplace(MarketplacePluginRequest $request, Server $server, string $platform, string $project): JsonResponse
    {
        $resolved = $this->marketplace->resolveInstall(
            $platform,
            $project,
            $request->input('version_id'),
            (string) $request->input('version', ''),
            (string) $request->input('loader', '')
        );

        $this->pullToPlugins($server, $resolved['url'], $resolved['filename']);

        Activity::event('server:plugin.marketplace-install')
            ->property('platform', $platform)
            ->property('project', $project)
            ->property('version', $resolved['version'])
            ->property('filename', $resolved['filename'])
            ->log();

        return new JsonResponse(['filename' => $resolved['filename']], JsonResponse::HTTP_ACCEPTED);
    }

    private function pullToPlugins(Server $server, string $url, string $filename): void
    {
        $repository = $this->fileRepository->setServer($server);
        $files = [];

        try {
            $files = $repository->getDirectory('/plugins');
        } catch (\Throwable) {
            $repository->createDirectory('plugins', '/');
        }

        foreach ($files as $file) {
            if (Arr::get($file, 'file', true) && Arr::get($file, 'name') === $filename) {
                throw new BadRequestHttpException("{$filename} already exists in /plugins.");
            }
        }

        $repository->pull($url, '/plugins', [
            'filename' => $filename,
            'foreground' => true,
        ]);
    }

    private function transform(ArixPlugin $plugin): array
    {
        return [
            'object' => 'arix_plugin',
            'attributes' => [
                'id' => $plugin->id,
                'name' => $plugin->name,
                'description' => $plugin->description,
                'filename' => $plugin->filename,
                'icon_url' => $plugin->icon_url,
            ],
        ];
    }
}

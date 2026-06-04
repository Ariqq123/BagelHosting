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
    private const MARKETPLACE_MANIFEST = '/plugins/.plugin-history.json';
    private const LEGACY_MARKETPLACE_MANIFEST = '/plugins/.ptero-marketplace.json';
    private const MARKETPLACE_MANIFEST_NOTICE = '_notice';
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
        $this->recordMarketplaceInstall($server, $resolved['filename'], $platform, $project, $resolved['version']);

        Activity::event('server:plugin.marketplace-install')
            ->property('platform', $platform)
            ->property('project', $project)
            ->property('version', $resolved['version'])
            ->property('filename', $resolved['filename'])
            ->log();

        return new JsonResponse(['filename' => $resolved['filename']], JsonResponse::HTTP_ACCEPTED);
    }

    public function installedMarketplace(MarketplacePluginRequest $request, Server $server): array
    {
        $repository = $this->fileRepository->setServer($server);
        $files = $this->pluginFiles($repository);
        $manifest = $this->readMarketplaceManifest($repository);

        return [
            'data' => collect($files)->map(function (string $filename) use ($manifest) {
                $record = Arr::get($manifest, $filename);
                $latest = null;
                $updateAvailable = false;

                if (is_array($record)) {
                    $versions = $this->marketplace->versions((string) $record['platform'], (string) $record['project']);
                    $latest = Arr::get($versions, '0.id');
                    $updateAvailable = $latest && $latest !== Arr::get($record, 'version');
                }

                return [
                    'filename' => $filename,
                    'tracked' => is_array($record),
                    'platform' => Arr::get($record, 'platform'),
                    'project' => Arr::get($record, 'project'),
                    'version' => Arr::get($record, 'version'),
                    'latestVersion' => $latest,
                    'updateAvailable' => $updateAvailable,
                ];
            })->values()->all(),
        ];
    }

    public function updateInstalledMarketplace(MarketplacePluginRequest $request, Server $server): JsonResponse
    {
        $filename = (string) $request->input('filename');
        $repository = $this->fileRepository->setServer($server);
        $manifest = $this->readMarketplaceManifest($repository);
        $record = Arr::get($manifest, $filename);

        if (!is_array($record)) {
            throw new BadRequestHttpException('This plugin is not linked to a marketplace install.');
        }

        $resolved = $this->marketplace->resolveInstall((string) $record['platform'], (string) $record['project'], null);

        if ($resolved['version'] === Arr::get($record, 'version')) {
            return new JsonResponse(['filename' => $filename, 'updated' => false], JsonResponse::HTTP_OK);
        }

        $repository->deleteFiles('/plugins', [$filename]);
        $this->pullToPlugins($server, $resolved['url'], $resolved['filename']);

        unset($manifest[$filename]);
        $manifest[$resolved['filename']] = [
            'platform' => $record['platform'],
            'project' => $record['project'],
            'version' => $resolved['version'],
        ];
        $this->writeMarketplaceManifest($repository, $manifest);

        Activity::event('server:plugin.marketplace-update')
            ->property('platform', $record['platform'])
            ->property('project', $record['project'])
            ->property('from_filename', $filename)
            ->property('filename', $resolved['filename'])
            ->property('version', $resolved['version'])
            ->log();

        return new JsonResponse(['filename' => $resolved['filename'], 'updated' => true], JsonResponse::HTTP_ACCEPTED);
    }

    public function renameInstalledMarketplace(MarketplacePluginRequest $request, Server $server): JsonResponse
    {
        $from = $this->sanitizePluginFilename((string) $request->input('from'));
        $to = $this->sanitizePluginFilename((string) $request->input('to'));
        $repository = $this->fileRepository->setServer($server);

        $repository->renameFiles('/plugins', [['from' => $from, 'to' => $to]]);

        $manifest = $this->readMarketplaceManifest($repository);
        if (isset($manifest[$from])) {
            $manifest[$to] = $manifest[$from];
            unset($manifest[$from]);
            $this->writeMarketplaceManifest($repository, $manifest);
        }

        Activity::event('server:plugin.marketplace-rename')
            ->property('from_filename', $from)
            ->property('filename', $to)
            ->log();

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    public function deleteInstalledMarketplace(MarketplacePluginRequest $request, Server $server): JsonResponse
    {
        $filename = $this->sanitizePluginFilename((string) $request->input('filename'));
        $repository = $this->fileRepository->setServer($server);

        $repository->deleteFiles('/plugins', [$filename]);

        $manifest = $this->readMarketplaceManifest($repository);
        if (isset($manifest[$filename])) {
            unset($manifest[$filename]);
            $this->writeMarketplaceManifest($repository, $manifest);
        }

        Activity::event('server:plugin.marketplace-delete')
            ->property('filename', $filename)
            ->log();

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    private function sanitizePluginFilename(string $filename): string
    {
        $filename = basename($filename);

        if ($filename === '' || !str_ends_with(strtolower($filename), '.jar')) {
            throw new BadRequestHttpException('Invalid plugin filename.');
        }

        return $filename;
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

    private function pluginFiles(DaemonFileRepository $repository): array
    {
        try {
            $files = $repository->getDirectory('/plugins');
        } catch (\Throwable) {
            return [];
        }

        return collect($files)
            ->filter(fn (array $file) => Arr::get($file, 'file', true) && str_ends_with(strtolower((string) Arr::get($file, 'name')), '.jar'))
            ->pluck('name')
            ->values()
            ->all();
    }

    private function recordMarketplaceInstall(Server $server, string $filename, string $platform, string $project, string $version): void
    {
        $repository = $this->fileRepository->setServer($server);
        $manifest = $this->readMarketplaceManifest($repository);
        $manifest[$filename] = [
            'platform' => $platform,
            'project' => $project,
            'version' => $version,
        ];

        $this->writeMarketplaceManifest($repository, $manifest);
    }

    private function readMarketplaceManifest(DaemonFileRepository $repository): array
    {
        try {
            $content = $repository->getContent(self::MARKETPLACE_MANIFEST, 1024 * 1024);
        } catch (\Throwable) {
            try {
                $content = $repository->getContent(self::LEGACY_MARKETPLACE_MANIFEST, 1024 * 1024);
            } catch (\Throwable) {
                return [];
            }
        }

        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function writeMarketplaceManifest(DaemonFileRepository $repository, array $manifest): void
    {
        unset($manifest[self::MARKETPLACE_MANIFEST_NOTICE]);

        $manifest = [
            self::MARKETPLACE_MANIFEST_NOTICE => 'Do not delete or modify this file. Plugin update tracking will be disabled if this file is removed or changed incorrectly.',
            ...$manifest,
        ];

        $repository->putContent(self::MARKETPLACE_MANIFEST, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
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

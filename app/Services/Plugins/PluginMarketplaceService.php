<?php

namespace Pterodactyl\Services\Plugins;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PluginMarketplaceService
{
    private Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'timeout' => config('pterodactyl.guzzle.timeout'),
            'connect_timeout' => config('pterodactyl.guzzle.connect_timeout'),
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'Pterodactyl-Plugin-Marketplace/1.0',
            ],
            'http_errors' => false,
            'allow_redirects' => false,
        ]);
    }

    public function search(string $platform, string $query, int $page = 1, string $version = '', string $loader = ''): array
    {
        $platform = $this->normalizePlatform($platform);
        $query = trim($query);
        $page = max(1, min($page, 25));

        return Cache::remember(
            sprintf('plugin-marketplace:search:v5:%s:%s:%s:%s:%d', $platform, md5($query), $version, $loader, $page),
            300,
            fn () => $platform === 'modrinth' ? $this->searchModrinth($query, $page, $version, $loader) : $this->searchSpiget($query, $page)
        );
    }

    public function versions(string $platform, string $project, string $gameVersion = '', string $loader = ''): array
    {
        $platform = $this->normalizePlatform($platform);

        return Cache::remember(
            sprintf('plugin-marketplace:versions:v5:%s:%s:%s:%s', $platform, md5($project), $gameVersion, $loader),
            300,
            fn () => $platform === 'modrinth' ? $this->modrinthVersions($project, $gameVersion, $loader) : $this->spigetVersions($project)
        );
    }

    public function resolveInstall(string $platform, string $project, ?string $version, string $gameVersion = '', string $loader = ''): array
    {
        $platform = $this->normalizePlatform($platform);
        $versions = $this->versions($platform, $project, $gameVersion, $loader);
        $selected = collect($versions)->first(fn (array $item) => $version ? $item['id'] === $version : true);

        if (!$selected) {
            throw new BadRequestHttpException('No compatible plugin version was found.');
        }

        if (!str_ends_with(strtolower($selected['filename']), '.jar')) {
            throw new BadRequestHttpException('The selected version does not provide a .jar file.');
        }

        return [
            'url' => $selected['downloadUrl'],
            'filename' => $this->sanitizeFilename($selected['filename']),
            'version' => $selected['id'],
        ];
    }

    private function searchModrinth(string $query, int $page, string $version, string $loader): array
    {
        $facets = [['project_type:plugin']];
        if ($version !== '') $facets[] = ["versions:{$version}"];
        if ($loader !== '') $facets[] = ["categories:{$loader}"];

        $params = [
            'limit' => 12,
            'offset' => ($page - 1) * 12,
            'facets' => json_encode($facets),
            'index' => 'downloads',
        ];
        if ($query !== '') {
            $params['query'] = $query;
        }

        $json = $this->getJson('https://api.modrinth.com/v2/search', $params);

        return [
            'data' => collect(Arr::get($json, 'hits', []))->map(fn (array $project) => [
                'platform' => 'modrinth',
                'id' => (string) Arr::get($project, 'project_id'),
                'slug' => (string) Arr::get($project, 'slug'),
                'name' => (string) Arr::get($project, 'title'),
                'author' => (string) Arr::get($project, 'author'),
                'description' => (string) Arr::get($project, 'description'),
                'iconUrl' => Arr::get($project, 'icon_url'),
                'downloads' => (int) Arr::get($project, 'downloads', 0),
                'stars' => (int) Arr::get($project, 'follows', 0),
                'updatedAt' => Arr::get($project, 'date_modified'),
                'url' => 'https://modrinth.com/plugin/' . Arr::get($project, 'slug'),
                'installed' => false,
                'external' => false,
            ])->values()->all(),
            'meta' => ['page' => $page, 'perPage' => 12, 'total' => (int) Arr::get($json, 'total_hits', 0)],
        ];
    }

    private function searchSpiget(string $query, int $page): array
    {
        if ($query === '') {
            $json = $this->getJson('https://api.spiget.org/v2/resources', [
                'page' => $page,
                'size' => 24,
                'sort' => '-downloads',
            ]);
        } else {
            try {
                $json = $this->getJson('https://api.spiget.org/v2/search/resources/free/' . rawurlencode($query), [
                    'page' => $page,
                    'size' => 12,
                    'sort' => '-downloads',
                ]);
            } catch (BadRequestHttpException) {
                $json = $this->getJson('https://api.spiget.org/v2/search/resources/' . rawurlencode($query), [
                    'page' => $page,
                    'size' => 24,
                    'sort' => '-downloads',
                ]);
            }
        }

        return [
            'data' => collect($json)->filter(fn (array $project) => !(bool) Arr::get($project, 'premium', false))->take(12)->map(fn (array $project) => [
                'platform' => 'spiget',
                'id' => (string) Arr::get($project, 'id'),
                'slug' => (string) Arr::get($project, 'id'),
                'name' => (string) Arr::get($project, 'name'),
                'author' => $this->spigetAuthorName((int) Arr::get($project, 'id'), Arr::get($project, 'author')),
                'description' => (string) Arr::get($project, 'tag', ''),
                'iconUrl' => Arr::get($project, 'icon.url') ? 'https://www.spigotmc.org/' . ltrim(Arr::get($project, 'icon.url'), '/') : null,
                'downloads' => (int) Arr::get($project, 'downloads', 0),
                'stars' => round((float) Arr::get($project, 'rating.average', 0), 1),
                'updatedAt' => Arr::get($project, 'updateDate') ? date(DATE_ATOM, (int) Arr::get($project, 'updateDate')) : null,
                'url' => 'https://www.spigotmc.org/resources/' . Arr::get($project, 'id'),
                'installed' => false,
                'external' => (bool) Arr::get($project, 'external', false),
            ])->values()->all(),
            'meta' => ['page' => $page, 'perPage' => 12, 'total' => null],
        ];
    }

    private function spigetAuthorName(int $resource, mixed $author): string
    {
        if ($resource > 0) {
            return Cache::remember("plugin-marketplace:spiget-resource-author:{$resource}", 86400, function () use ($resource, $author) {
                try {
                    $json = $this->getJson("https://api.spiget.org/v2/resources/{$resource}/author");
                } catch (BadRequestHttpException) {
                    return $this->spigetFallbackAuthor($author);
                }

                return (string) Arr::get($json, 'name', $this->spigetFallbackAuthor($author));
            });
        }

        return $this->spigetFallbackAuthor($author);
    }

    private function spigetFallbackAuthor(mixed $author): string
    {
        if (is_array($author) && Arr::get($author, 'name')) {
            return (string) Arr::get($author, 'name');
        }

        return (string) (is_array($author) ? Arr::get($author, 'id', '') : $author);
    }

    private function modrinthVersions(string $project, string $gameVersion, string $loader): array
    {
        $query = [];
        if ($gameVersion !== '') $query['game_versions'] = json_encode([$gameVersion]);
        if ($loader !== '') $query['loaders'] = json_encode([$loader]);

        $json = $this->getJson("https://api.modrinth.com/v2/project/{$project}/version", $query);

        return collect($json)->map(function (array $version) {
            $file = collect(Arr::get($version, 'files', []))->first(fn (array $file) => (bool) Arr::get($file, 'primary') && str_ends_with(strtolower((string) Arr::get($file, 'filename')), '.jar'))
                ?? collect(Arr::get($version, 'files', []))->first(fn (array $file) => str_ends_with(strtolower((string) Arr::get($file, 'filename')), '.jar'));

            if (!$file) return null;

            return [
                'id' => (string) Arr::get($version, 'id'),
                'name' => (string) Arr::get($version, 'name'),
                'versionNumber' => (string) Arr::get($version, 'version_number'),
                'createdAt' => Arr::get($version, 'date_published'),
                'filename' => (string) Arr::get($file, 'filename'),
                'downloadUrl' => (string) Arr::get($file, 'url'),
            ];
        })->filter()->values()->all();
    }

    private function spigetVersions(string $project): array
    {
        $resource = $this->getJson("https://api.spiget.org/v2/resources/{$project}");
        $fallback = $this->sanitizeBase((string) Arr::get($resource, 'name', $project)) . '.jar';

        if ((bool) Arr::get($resource, 'external')) {
            $externalUrl = (string) Arr::get($resource, 'file.externalUrl', '');
            if (!filter_var($externalUrl, FILTER_VALIDATE_URL)) {
                throw new BadRequestHttpException('External SpiGet download URL is invalid.');
            }

            $versions = $this->getJson("https://api.spiget.org/v2/resources/{$project}/versions", ['size' => 20, 'sort' => '-releaseDate']);
            $currentVersion = (string) Arr::get($resource, 'version.name', Arr::get($versions, '0.name', ''));
            $versions = $versions ?: [[
                'id' => Arr::get($resource, 'version.id', 'external'),
                'name' => $currentVersion ?: 'External Download',
                'releaseDate' => Arr::get($resource, 'updateDate'),
            ]];

            return collect($versions)->map(function (array $version) use ($externalUrl, $currentVersion, $fallback) {
                $versionName = (string) Arr::get($version, 'name', 'External Download');
                $candidate = $currentVersion !== '' ? str_replace($currentVersion, $versionName, $externalUrl) : $externalUrl;
                $downloadUrl = $this->resolveDownloadUrl($candidate);

                if (!$downloadUrl) {
                    return null;
                }

                return [
                    'id' => (string) Arr::get($version, 'id', $versionName),
                    'name' => $versionName,
                    'versionNumber' => $versionName,
                    'createdAt' => Arr::get($version, 'releaseDate') ? date(DATE_ATOM, (int) Arr::get($version, 'releaseDate')) : null,
                    'filename' => $this->externalFilename($candidate, $fallback),
                    'downloadUrl' => $downloadUrl,
                ];
            })->filter()->values()->all();
        }

        $versions = $this->getJson("https://api.spiget.org/v2/resources/{$project}/versions", ['size' => 20, 'sort' => '-releaseDate']);

        return collect($versions ?: [['id' => 'latest', 'name' => Arr::get($resource, 'version.name', 'latest')]])->map(fn (array $version) => [
            'id' => (string) Arr::get($version, 'id', 'latest'),
            'name' => (string) Arr::get($version, 'name', 'Latest'),
            'versionNumber' => (string) Arr::get($version, 'name', 'latest'),
            'createdAt' => Arr::get($version, 'releaseDate') ? date(DATE_ATOM, (int) Arr::get($version, 'releaseDate')) : null,
            'filename' => $fallback,
            'downloadUrl' => "https://api.spiget.org/v2/resources/{$project}/download",
        ])->values()->all();
    }

    private function resolveDownloadUrl(string $url): ?string
    {
        $effectiveUrl = $url;

        try {
            $response = $this->http->head($url, [
                'allow_redirects' => ['max' => 5, 'track_redirects' => true],
                'on_stats' => function (TransferStats $stats) use (&$effectiveUrl) {
                    $effectiveUrl = (string) $stats->getEffectiveUri();
                },
            ]);
        } catch (TransferException) {
            return null;
        }

        return $response->getStatusCode() < 400 && filter_var($effectiveUrl, FILTER_VALIDATE_URL) ? $effectiveUrl : null;
    }

    private function getJson(string $url, array $query = []): array
    {
        try {
            $response = $this->http->get($url, ['query' => $query]);
        } catch (TransferException $exception) {
            throw new BadRequestHttpException('Marketplace provider is unavailable.', $exception);
        }

        if ($response->getStatusCode() >= 400) {
            throw new BadRequestHttpException('Marketplace provider returned an error.');
        }

        return json_decode((string) $response->getBody(), true) ?: [];
    }

    private function normalizePlatform(string $platform): string
    {
        $platform = strtolower($platform);
        if (!in_array($platform, ['modrinth', 'spiget'], true)) {
            throw new BadRequestHttpException('Unsupported marketplace platform.');
        }

        return $platform;
    }

    private function externalFilename(string $url, string $fallback): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $name = basename($path);

        return str_ends_with(strtolower($name), '.jar') ? $this->sanitizeFilename($name) : $fallback;
    }

    private function sanitizeBase(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9._-]+/', '-', $value) ?: 'plugin';

        return trim($value, '.-') ?: 'plugin';
    }

    private function sanitizeFilename(string $value): string
    {
        $base = basename($value);
        $base = preg_replace('/[^A-Za-z0-9._-]+/', '-', $base) ?: 'plugin.jar';

        return str_ends_with(strtolower($base), '.jar') ? $base : $base . '.jar';
    }
}

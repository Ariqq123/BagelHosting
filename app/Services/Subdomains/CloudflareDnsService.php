<?php

namespace Pterodactyl\Services\Subdomains;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Pterodactyl\Models\SubdomainDomain;
use Pterodactyl\Exceptions\DisplayException;

class CloudflareDnsService
{
    private const BASE_URL = 'https://api.cloudflare.com/client/v4';

    /**
     * @throws DisplayException
     */
    public function listZones(string $token): array
    {
        $zones = [];
        $page = 1;

        do {
            $response = Http::withToken($token)
                ->acceptJson()
                ->get(sprintf('%s/zones', self::BASE_URL), [
                    'page' => $page,
                    'per_page' => 50,
                ]);

            $payload = $response->json() ?? [];
            if (!$response->successful() || !Arr::get($payload, 'success')) {
                throw new DisplayException($this->getErrorMessage($payload, 'Cloudflare failed to list zones.'));
            }

            foreach (Arr::get($payload, 'result', []) as $zone) {
                if (is_array($zone) && !empty($zone['id']) && !empty($zone['name'])) {
                    $zones[] = [
                        'id' => $zone['id'],
                        'name' => $zone['name'],
                    ];
                }
            }

            $totalPages = (int) Arr::get($payload, 'result_info.total_pages', 1);
            ++$page;
        } while ($page <= $totalPages);

        return $zones;
    }

    /**
     * @throws DisplayException
     */
    public function createRecord(SubdomainDomain $domain, string $type, string $fqdn, string $content, bool $proxied): string
    {
        return $this->createDnsRecord($domain, [
            'type' => $type,
            'name' => $fqdn,
            'content' => $content,
            'proxied' => $proxied,
            'ttl' => 1,
        ], 'Cloudflare failed to create the DNS record.');
    }

    /**
     * @throws DisplayException
     */
    public function createMinecraftSrvRecord(SubdomainDomain $domain, string $fqdn, int $port): string
    {
        $relativeName = str_ends_with($fqdn, '.' . $domain->name)
            ? substr($fqdn, 0, -1 * (strlen($domain->name) + 1))
            : $fqdn;

        return $this->createDnsRecord($domain, [
            'type' => 'SRV',
            'name' => sprintf('_minecraft._tcp.%s', $fqdn),
            'data' => [
                'service' => '_minecraft',
                'proto' => '_tcp',
                'name' => $relativeName,
                'priority' => 0,
                'weight' => 0,
                'port' => $port,
                'target' => $fqdn,
            ],
            'ttl' => 1,
        ], 'Cloudflare failed to create the Minecraft SRV record.');
    }

    /**
     * @throws DisplayException
     */
    public function updateRecord(SubdomainDomain $domain, string $recordId, string $type, string $fqdn, string $content, bool $proxied): void
    {
        $response = Http::withToken($domain->cloudflare_token)
            ->acceptJson()
            ->asJson()
            ->put(sprintf('%s/zones/%s/dns_records/%s', self::BASE_URL, $domain->cloudflare_zone_id, $recordId), [
                'type' => $type,
                'name' => $fqdn,
                'content' => $content,
                'proxied' => $proxied,
                'ttl' => 1,
            ]);

        $payload = $response->json() ?? [];
        if (!$response->successful() || !Arr::get($payload, 'success')) {
            throw new DisplayException($this->getErrorMessage($payload, 'Cloudflare failed to update the DNS record.'));
        }
    }

    /**
     * @throws DisplayException
     */
    public function deleteRecord(SubdomainDomain $domain, string $recordId): void
    {
        $response = Http::withToken($domain->cloudflare_token)
            ->acceptJson()
            ->delete(sprintf('%s/zones/%s/dns_records/%s', self::BASE_URL, $domain->cloudflare_zone_id, $recordId));

        $payload = $response->json() ?? [];
        if (!$response->successful() || !Arr::get($payload, 'success')) {
            throw new DisplayException($this->getErrorMessage($payload, 'Cloudflare failed to delete the DNS record.'));
        }
    }

    /**
     * @throws DisplayException
     */
    private function createDnsRecord(SubdomainDomain $domain, array $data, string $fallback): string
    {
        $response = Http::withToken($domain->cloudflare_token)
            ->acceptJson()
            ->asJson()
            ->post(sprintf('%s/zones/%s/dns_records', self::BASE_URL, $domain->cloudflare_zone_id), $data);

        $payload = $response->json() ?? [];
        if (!$response->successful() || !Arr::get($payload, 'success')) {
            throw new DisplayException($this->getErrorMessage($payload, $fallback));
        }

        $id = Arr::get($payload, 'result.id');
        if (!is_string($id) || $id === '') {
            throw new DisplayException('Cloudflare did not return a DNS record ID.');
        }

        return $id;
    }

    private function getErrorMessage(array $payload, string $fallback): string
    {
        $messages = collect(Arr::get($payload, 'errors', []))
            ->pluck('message')
            ->filter()
            ->implode(' ');

        return $messages !== '' ? $messages : $fallback;
    }
}

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
    public function createRecord(SubdomainDomain $domain, string $type, string $fqdn, string $content, bool $proxied): string
    {
        $response = Http::withToken($domain->cloudflare_token)
            ->acceptJson()
            ->asJson()
            ->post(sprintf('%s/zones/%s/dns_records', self::BASE_URL, $domain->cloudflare_zone_id), [
                'type' => $type,
                'name' => $fqdn,
                'content' => $content,
                'proxied' => $proxied,
                'ttl' => 1,
            ]);

        $payload = $response->json() ?? [];
        if (!$response->successful() || !Arr::get($payload, 'success')) {
            throw new DisplayException($this->getErrorMessage($payload, 'Cloudflare failed to create the DNS record.'));
        }

        $id = Arr::get($payload, 'result.id');
        if (!is_string($id) || $id === '') {
            throw new DisplayException('Cloudflare did not return a DNS record ID.');
        }

        return $id;
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

    private function getErrorMessage(array $payload, string $fallback): string
    {
        $messages = collect(Arr::get($payload, 'errors', []))
            ->pluck('message')
            ->filter()
            ->implode(' ');

        return $messages !== '' ? $messages : $fallback;
    }
}

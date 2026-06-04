<?php

namespace Pterodactyl\Services\Subdomains;

use Illuminate\Database\ConnectionInterface;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Subdomain;
use Pterodactyl\Models\SubdomainDomain;
use Pterodactyl\Models\User;

class SubdomainManagementService
{
    public function __construct(
        private ConnectionInterface $connection,
        private CloudflareDnsService $cloudflare,
    ) {
    }

    /**
     * @throws DisplayException
     * @throws \Throwable
     */
    public function create(Server $server, User $user, SubdomainDomain $domain, string $name, string $type): Subdomain
    {
        $name = strtolower($name);
        $type = strtoupper($type);
        $fqdn = sprintf('%s.%s', $name, $domain->name);

        return $this->connection->transaction(function () use ($server, $user, $domain, $name, $type, $fqdn) {
            if (!$domain->enabled) {
                throw new DisplayException('This domain is not currently available for subdomain creation.');
            }

            if (!in_array($type, $domain->allowed_record_types ?? [], true)) {
                throw new DisplayException('This DNS record type is not available for the selected domain.');
            }

            if ($server->subdomains()->lockForUpdate()->count() >= ($server->subdomain_limit ?? 0)) {
                throw new DisplayException('Cannot create additional subdomains on this server: limit has been reached.');
            }

            if (Subdomain::query()->where('fqdn', $fqdn)->exists()) {
                throw new DisplayException('The selected subdomain is already in use.');
            }

            $content = $this->getRecordContent($server, $domain, $type);
            $recordId = $this->cloudflare->createRecord($domain, $type, $fqdn, $content, $domain->proxied);

            return Subdomain::query()->create([
                'server_id' => $server->id,
                'user_id' => $user->id,
                'subdomain_domain_id' => $domain->id,
                'name' => $name,
                'fqdn' => $fqdn,
                'type' => $type,
                'content' => $content,
                'proxied' => $domain->proxied,
                'cloudflare_record_id' => $recordId,
                'status' => Subdomain::STATUS_ACTIVE,
                'error_message' => null,
            ]);
        });
    }

    /**
     * @throws DisplayException
     */
    public function delete(Subdomain $subdomain): void
    {
        try {
            if (!empty($subdomain->cloudflare_record_id)) {
                $this->cloudflare->deleteRecord($subdomain->domain, $subdomain->cloudflare_record_id);
            }

            $subdomain->delete();
        } catch (DisplayException $exception) {
            $subdomain->forceFill([
                'status' => Subdomain::STATUS_ERROR,
                'error_message' => $exception->getMessage(),
            ])->save();

            throw $exception;
        }
    }

    /**
     * @throws DisplayException
     */
    private function getRecordContent(Server $server, SubdomainDomain $domain, string $type): string
    {
        if ($type === 'A') {
            return $server->allocation->ip;
        }

        if ($type === 'CNAME' && !empty($domain->cname_target)) {
            return $domain->cname_target;
        }

        throw new DisplayException('The selected domain is missing a CNAME target.');
    }
}

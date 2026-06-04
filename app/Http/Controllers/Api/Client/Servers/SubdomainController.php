<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\JsonResponse;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Subdomain;
use Pterodactyl\Models\SubdomainDomain;
use Pterodactyl\Services\Subdomains\SubdomainManagementService;
use Pterodactyl\Transformers\Api\Client\SubdomainTransformer;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Http\Requests\Api\Client\Servers\Subdomains\GetSubdomainsRequest;
use Pterodactyl\Http\Requests\Api\Client\Servers\Subdomains\StoreSubdomainRequest;
use Pterodactyl\Http\Requests\Api\Client\Servers\Subdomains\DeleteSubdomainRequest;

class SubdomainController extends ClientApiController
{
    public function __construct(private SubdomainManagementService $managementService)
    {
        parent::__construct();
    }

    public function index(GetSubdomainsRequest $request, Server $server): array
    {
        $subdomains = $this->fractal->collection($server->subdomains()->with('domain')->get())
            ->transformWith($this->getTransformer(SubdomainTransformer::class))
            ->toArray();

        $subdomains['meta']['domains'] = SubdomainDomain::query()
            ->where('enabled', true)
            ->orderBy('name')
            ->get()
            ->map(fn (SubdomainDomain $domain) => [
                'id' => $domain->id,
                'name' => $domain->name,
                'allowed_record_types' => $domain->allowed_record_types ?? [],
                'proxied' => $domain->proxied,
            ])
            ->values();

        $subdomains['meta']['limit'] = $server->subdomain_limit ?? 0;

        return $subdomains;
    }

    /**
     * @throws \Throwable
     */
    public function store(StoreSubdomainRequest $request, Server $server): array
    {
        /** @var SubdomainDomain $domain */
        $domain = SubdomainDomain::query()->findOrFail($request->input('domain_id'));

        $subdomain = Activity::event('server:subdomain.create')->transaction(function ($log) use ($request, $server, $domain) {
            $subdomain = $this->managementService->create(
                $server,
                $request->user(),
                $domain,
                $request->input('name'),
                $request->input('type'),
            );

            $log->subject($subdomain)->property('fqdn', $subdomain->fqdn);

            return $subdomain;
        });

        return $this->fractal->item($subdomain)
            ->transformWith($this->getTransformer(SubdomainTransformer::class))
            ->toArray();
    }

    public function delete(DeleteSubdomainRequest $request, Server $server, Subdomain $subdomain): JsonResponse
    {
        $fqdn = $subdomain->fqdn;

        $this->managementService->delete($subdomain);

        Activity::event('server:subdomain.delete')
            ->property('fqdn', $fqdn)
            ->log();

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }
}

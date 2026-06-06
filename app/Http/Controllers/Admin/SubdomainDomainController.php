<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Models\SubdomainDomain;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Services\Subdomains\CloudflareDnsService;
use Pterodactyl\Http\Requests\Admin\SubdomainDomainFormRequest;
use Pterodactyl\Http\Requests\Admin\ImportSubdomainDomainsRequest;

class SubdomainDomainController extends Controller
{
    public function __construct(private AlertsMessageBag $alert)
    {
    }

    public function index(): View
    {
        return view('admin.subdomains.index', [
            'domains' => SubdomainDomain::query()->withCount('subdomains')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.subdomains.form', [
            'domain' => new SubdomainDomain([
                'allowed_record_types' => ['A'],
                'proxied' => false,
                'enabled' => true,
            ]),
            'mode' => 'create',
        ]);
    }

    public function store(SubdomainDomainFormRequest $request): RedirectResponse
    {
        $domain = SubdomainDomain::query()->create(array_filter(
            $request->normalize(),
            fn ($value, $key) => $key !== 'cloudflare_token' || !is_null($value),
            ARRAY_FILTER_USE_BOTH,
        ));

        $this->alert->success('Subdomain domain was created successfully.')->flash();

        return redirect()->route('admin.subdomains.edit', $domain->id);
    }

    public function previewImport(ImportSubdomainDomainsRequest $request, CloudflareDnsService $cloudflare): View
    {
        $zones = collect($cloudflare->listZones($request->input('cloudflare_token')))
            ->sortBy('name')
            ->values();

        return view('admin.subdomains.index', [
            'domains' => SubdomainDomain::query()->withCount('subdomains')->orderBy('name')->get(),
            'importToken' => $request->input('cloudflare_token'),
            'importZones' => $zones,
            'existingDomainNames' => SubdomainDomain::query()->pluck('name')->all(),
        ]);
    }

    public function import(ImportSubdomainDomainsRequest $request): RedirectResponse
    {
        $created = 0;

        foreach ($request->input('zones', []) as $zone) {
            if (empty($zone['selected'])) {
                continue;
            }

            $name = strtolower($zone['name']);
            $domain = SubdomainDomain::query()->firstOrNew(['name' => $name]);
            if ($domain->exists) {
                continue;
            }

            $domain->forceFill([
                'cloudflare_zone_id' => $zone['id'],
                'cloudflare_token' => $request->input('cloudflare_token'),
                'allowed_record_types' => ['A'],
                'cname_target' => null,
                'proxied' => false,
                'enabled' => true,
            ])->save();

            ++$created;
        }

        $this->alert->success(sprintf('Imported %d Cloudflare domain%s.', $created, $created === 1 ? '' : 's'))->flash();

        return redirect()->route('admin.subdomains');
    }

    public function edit(SubdomainDomain $domain): View
    {
        $domain->load([
            'subdomains' => fn ($query) => $query->with(['server.user', 'user'])->orderBy('fqdn'),
        ]);

        return view('admin.subdomains.form', [
            'domain' => $domain,
            'mode' => 'edit',
        ]);
    }

    public function update(SubdomainDomainFormRequest $request, SubdomainDomain $domain): RedirectResponse
    {
        $data = $request->normalize();
        if (empty($data['cloudflare_token'])) {
            unset($data['cloudflare_token']);
        }

        $domain->forceFill($data)->save();
        $this->alert->success('Subdomain domain was updated successfully.')->flash();

        return redirect()->route('admin.subdomains.edit', $domain->id);
    }

    /**
     * @throws DisplayException
     */
    public function delete(SubdomainDomain $domain): RedirectResponse
    {
        if ($domain->subdomains()->exists()) {
            throw new DisplayException('Cannot delete a domain while subdomains are still using it.');
        }

        $domain->delete();
        $this->alert->success('Subdomain domain was deleted successfully.')->flash();

        return redirect()->route('admin.subdomains');
    }
}

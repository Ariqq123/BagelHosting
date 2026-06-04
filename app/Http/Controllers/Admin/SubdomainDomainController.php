<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Models\SubdomainDomain;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Http\Requests\Admin\SubdomainDomainFormRequest;

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

    public function edit(SubdomainDomain $domain): View
    {
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

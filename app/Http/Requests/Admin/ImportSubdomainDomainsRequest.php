<?php

namespace Pterodactyl\Http\Requests\Admin;

class ImportSubdomainDomainsRequest extends AdminFormRequest
{
    public function rules(): array
    {
        $zoneRule = $this->routeIs('admin.subdomains.import') ? ['required', 'array'] : ['sometimes', 'array'];

        return [
            'cloudflare_token' => ['required', 'string'],
            'zones' => $zoneRule,
            'zones.*.id' => ['required', 'string', 'max:191'],
            'zones.*.name' => ['required', 'string', 'max:191', 'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/'],
            'zones.*.selected' => ['sometimes', 'boolean'],
        ];
    }
}

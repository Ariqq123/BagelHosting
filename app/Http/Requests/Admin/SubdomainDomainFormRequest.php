<?php

namespace Pterodactyl\Http\Requests\Admin;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Pterodactyl\Models\SubdomainDomain;

class SubdomainDomainFormRequest extends AdminFormRequest
{
    public function rules(): array
    {
        $domain = $this->route()->parameter('domain');
        $domainId = $domain instanceof SubdomainDomain ? $domain->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:191',
                'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/',
                Rule::unique('subdomain_domains', 'name')->ignore($domainId),
            ],
            'cloudflare_zone_id' => ['required', 'string', 'max:191'],
            'cloudflare_token' => [$this->method() === 'POST' ? 'required' : 'nullable', 'string'],
            'allowed_record_types' => ['required', 'array'],
            'allowed_record_types.*' => ['required', 'string', Rule::in(['A', 'CNAME'])],
            'cname_target' => ['nullable', 'string', 'max:191'],
            'proxied' => ['sometimes', 'boolean'],
            'enabled' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (in_array('CNAME', $this->input('allowed_record_types', []), true) && empty($this->input('cname_target'))) {
                $validator->errors()->add('cname_target', 'A CNAME target is required when CNAME records are enabled.');
            }
        });
    }

    public function normalize(): array
    {
        $data = $this->validated();

        return [
            'name' => strtolower($data['name']),
            'cloudflare_zone_id' => $data['cloudflare_zone_id'],
            'cloudflare_token' => $data['cloudflare_token'] ?? null,
            'allowed_record_types' => array_values($data['allowed_record_types']),
            'cname_target' => $data['cname_target'] ?? null,
            'proxied' => $this->boolean('proxied'),
            'enabled' => $this->boolean('enabled'),
        ];
    }
}

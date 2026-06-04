<?php

namespace Pterodactyl\Http\Requests\Api\Client\Servers\Subdomains;

use Pterodactyl\Models\Server;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Pterodactyl\Models\Permission;
use Pterodactyl\Models\Subdomain;
use Pterodactyl\Models\SubdomainDomain;
use Pterodactyl\Contracts\Http\ClientPermissionsRequest;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

class StoreSubdomainRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    public function permission(): string
    {
        return Permission::ACTION_SUBDOMAIN_CREATE;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'regex:/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/'],
            'domain_id' => [
                'required',
                'integer',
                Rule::exists('subdomain_domains', 'id')->where('enabled', true),
            ],
            'type' => ['required', 'string', 'in:A,CNAME'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $domain = SubdomainDomain::query()->find($this->input('domain_id'));
            if (!$domain instanceof SubdomainDomain) {
                return;
            }

            $type = strtoupper((string) $this->input('type'));
            if (!in_array($type, $domain->allowed_record_types ?? [], true)) {
                $validator->errors()->add('type', 'The selected record type is not available for this domain.');
            }

            if ($type === 'CNAME' && empty($domain->cname_target)) {
                $validator->errors()->add('type', 'The selected domain does not have a CNAME target configured.');
            }

            $fqdn = strtolower(sprintf('%s.%s', $this->input('name'), $domain->name));
            if (Subdomain::query()->where('fqdn', $fqdn)->exists()) {
                $validator->errors()->add('name', 'The selected subdomain is already in use.');
            }

            $server = $this->route()->parameter('server');
            if ($server instanceof Server && $server->subdomains()->count() >= ($server->subdomain_limit ?? 0)) {
                $validator->errors()->add('name', 'This server has reached its subdomain limit.');
            }
        });
    }
}

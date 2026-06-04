<?php

namespace Pterodactyl\Transformers\Api\Client;

use Pterodactyl\Models\Subdomain;

class SubdomainTransformer extends BaseClientTransformer
{
    public function getResourceName(): string
    {
        return Subdomain::RESOURCE_NAME;
    }

    public function transform(Subdomain $model): array
    {
        $model->loadMissing('domain');

        return [
            'id' => $model->id,
            'name' => $model->name,
            'fqdn' => $model->fqdn,
            'type' => $model->type,
            'content' => $model->content,
            'proxied' => $model->proxied,
            'status' => $model->status,
            'error_message' => $model->error_message,
            'domain' => [
                'id' => $model->domain->id,
                'name' => $model->domain->name,
            ],
            'created_at' => $model->created_at?->toIso8601String(),
            'updated_at' => $model->updated_at?->toIso8601String(),
        ];
    }
}

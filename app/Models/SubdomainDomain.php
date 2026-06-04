<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $cloudflare_zone_id
 * @property string $cloudflare_token
 * @property array $allowed_record_types
 * @property string|null $cname_target
 * @property bool $proxied
 * @property bool $enabled
 */
class SubdomainDomain extends Model
{
    public const RESOURCE_NAME = 'subdomain_domain';

    protected $table = 'subdomain_domains';

    protected $fillable = [
        'name',
        'cloudflare_zone_id',
        'cloudflare_token',
        'allowed_record_types',
        'cname_target',
        'proxied',
        'enabled',
    ];

    protected $casts = [
        'allowed_record_types' => 'array',
        'cloudflare_token' => 'encrypted',
        'proxied' => 'boolean',
        'enabled' => 'boolean',
    ];

    public static array $validationRules = [
        'name' => 'required|string|max:191',
        'cloudflare_zone_id' => 'required|string|max:191',
        'cloudflare_token' => 'required|string',
        'allowed_record_types' => 'required|array',
        'cname_target' => 'nullable|string|max:191',
        'proxied' => 'boolean',
        'enabled' => 'boolean',
    ];

    public function subdomains(): HasMany
    {
        return $this->hasMany(Subdomain::class, 'subdomain_domain_id');
    }
}

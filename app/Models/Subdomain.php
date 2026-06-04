<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $server_id
 * @property int|null $user_id
 * @property int $subdomain_domain_id
 * @property string $name
 * @property string $fqdn
 * @property string $type
 * @property string $content
 * @property bool $proxied
 * @property string|null $cloudflare_record_id
 * @property string $status
 * @property string|null $error_message
 * @property Server $server
 * @property User|null $user
 * @property SubdomainDomain $domain
 */
class Subdomain extends Model
{
    public const RESOURCE_NAME = 'subdomain';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ERROR = 'error';

    protected $table = 'subdomains';

    protected $fillable = [
        'server_id',
        'user_id',
        'subdomain_domain_id',
        'name',
        'fqdn',
        'type',
        'content',
        'proxied',
        'cloudflare_record_id',
        'status',
        'error_message',
    ];

    protected $casts = [
        'server_id' => 'integer',
        'user_id' => 'integer',
        'subdomain_domain_id' => 'integer',
        'proxied' => 'boolean',
    ];

    public static array $validationRules = [
        'server_id' => 'required|numeric|exists:servers,id',
        'user_id' => 'nullable|numeric|exists:users,id',
        'subdomain_domain_id' => 'required|numeric|exists:subdomain_domains,id',
        'name' => 'required|string|between:1,63',
        'fqdn' => 'required|string|max:191|unique:subdomains,fqdn',
        'type' => 'required|string|in:A,CNAME',
        'content' => 'required|string|max:191',
        'proxied' => 'boolean',
        'cloudflare_record_id' => 'nullable|string|max:191',
        'status' => 'required|string|in:active,error',
        'error_message' => 'nullable|string',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(SubdomainDomain::class, 'subdomain_domain_id');
    }
}

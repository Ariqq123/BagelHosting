<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $download_url
 * @property string $filename
 * @property string|null $icon_url
 * @property bool $enabled
 */
class ArixPlugin extends Model
{
    protected $table = 'arix_plugins';

    protected $fillable = [
        'name',
        'description',
        'download_url',
        'filename',
        'icon_url',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public static array $validationRules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'download_url' => 'required|string|url|starts_with:http://,https://',
        'filename' => 'required|string|max:255|regex:/^[A-Za-z0-9._ -]+$/',
        'icon_url' => 'nullable|string|url|starts_with:http://,https://',
        'enabled' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }
}

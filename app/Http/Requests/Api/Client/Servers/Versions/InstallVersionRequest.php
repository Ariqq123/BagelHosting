<?php

namespace Pterodactyl\Http\Requests\Api\Client\Servers\Versions;

use Pterodactyl\Models\Permission;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

class InstallVersionRequest extends ClientApiRequest
{
    public function permission(): string
    {
        return Permission::ACTION_SETTINGS_REINSTALL;
    }

    public function rules(): array
    {
        return [
            'egg_id' => 'required|integer|exists:eggs,id',
            'version' => 'required|string|max:32|regex:/^[A-Za-z0-9._+-]+$/',
        ];
    }
}

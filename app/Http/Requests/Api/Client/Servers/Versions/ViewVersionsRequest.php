<?php

namespace Pterodactyl\Http\Requests\Api\Client\Servers\Versions;

use Pterodactyl\Models\Permission;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

class ViewVersionsRequest extends ClientApiRequest
{
    public function permission(): string
    {
        return Permission::ACTION_SETTINGS_REINSTALL;
    }
}

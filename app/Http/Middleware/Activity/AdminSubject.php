<?php

namespace Pterodactyl\Http\Middleware\Activity;

use Closure;
use Pterodactyl\Facades\LogTarget;

class AdminSubject
{
    public function handle($request, Closure $next)
    {
        if ($user = $request->user()) {
            LogTarget::setActor($user);
        }

        return $next($request);
    }
}
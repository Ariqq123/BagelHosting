<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Models\ActivityLog;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::query()
            ->whereHas('actor', function ($q) {
                $q->where('root_admin', true);
            })
            ->with('actor')
            ->orderBy('timestamp', 'desc')
            ->paginate(25);

        return view('admin.logs.index', ['logs' => $logs]);
    }
}

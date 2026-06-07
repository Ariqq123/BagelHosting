<?php

namespace Pterodactyl\Http\Controllers\Admin\Extensions\freeservers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Pterodactyl\Models\Egg;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\Nest;
use Pterodactyl\Models\User;
use Pterodactyl\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary as BlueprintExtensionLibrary;
use Pterodactyl\BlueprintFramework\Extensions\freeservers\TranslationHelper as T;

class freeserversExtensionController extends Controller
{
    public function __construct(
        private ViewFactory $view,
        private BlueprintExtensionLibrary $blueprint,
    ) {}

    public function index(): View
    {
        if (!Schema::hasTable('freeservers_settings')) {
            return $this->view->make('admin.extensions.freeservers.index', [
                'settings' => null,
                'allowedEggs' => collect([]),
                'eggs' => collect([]),
                'nodes' => collect([]),
                'nests' => collect([]),
                'freeServersCount' => 0,
                'usersWithFreeServers' => 0,
                'blueprint' => $this->blueprint,
                'root' => '/admin/extensions/freeservers',
                'error' => T::t('alert_db_error'),
            ]);
        }

        $settings = DB::table('freeservers_settings')->first();
        
        if (!$settings) {
            DB::table('freeservers_settings')->insert([
                'enabled' => 0,
                'max_servers_per_user' => 1,
                'default_memory' => 1024,
                'default_disk' => 5120,
                'default_cpu' => 100,
                'default_swap' => 0,
                'default_io' => 500,
                'default_databases' => 0,
                'default_allocations' => 1,
                'default_backups' => 0,
                'allowed_nodes' => '[]',
                'server_description' => 'Free Server created via Free Servers Extension',
                'display_unit' => 'MB',
                'language' => 'en',
                'expiration_days' => 0,
                'extension_days' => 30,
                'allow_extension' => true,
                'enable_stats' => true,
                'discord_webhook_url' => null,
                'discord_notify_create' => false,
                'discord_notify_delete' => false,
                'discord_notify_expire' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $settings = DB::table('freeservers_settings')->first();
        }

        $allowedEggs = Schema::hasTable('freeservers_allowed_eggs') 
            ? DB::table('freeservers_allowed_eggs')
                ->leftJoin('eggs', 'freeservers_allowed_eggs.egg_id', '=', 'eggs.id')
                ->select('freeservers_allowed_eggs.*', 'eggs.name as egg_name')
                ->get()
            : collect([]);

        // Get user limits with related data
        $userLimits = Schema::hasTable('freeservers_user_limits')
            ? DB::table('freeservers_user_limits')
                ->join('users', 'freeservers_user_limits.user_id', '=', 'users.id')
                ->leftJoin(DB::raw('(SELECT user_id, COUNT(*) as server_count FROM freeservers_servers GROUP BY user_id) as server_counts'), 'freeservers_user_limits.user_id', '=', 'server_counts.user_id')
                ->select(
                    'freeservers_user_limits.id',
                    'freeservers_user_limits.user_id',
                    'freeservers_user_limits.max_servers',
                    'users.username',
                    'users.email',
                    DB::raw('COALESCE(server_counts.server_count, 0) as current_servers')
                )
                ->orderBy('users.username')
                ->get()
            : collect([]);

        return $this->view->make('admin.extensions.freeservers.index', [
            'settings' => $settings,
            'allowedEggs' => $allowedEggs,
            'eggs' => Egg::all(),
            'nodes' => Node::all(),
            'nests' => Nest::with('eggs')->get(),
            'freeServersCount' => Schema::hasTable('freeservers_servers') ? DB::table('freeservers_servers')->count() : 0,
            'usersWithFreeServers' => Schema::hasTable('freeservers_servers') ? DB::table('freeservers_servers')->distinct('user_id')->count() : 0,
            'userLimits' => $userLimits,
            'stats' => $this->stats(),
            'blueprint' => $this->blueprint,
            'root' => '/admin/extensions/freeservers',
            'error' => null,
            'lang' => T::all(),
        ]);
    }

    public function post(Request $request): RedirectResponse|JsonResponse
    {
        $action = $request->input('action');

        switch ($action) {
            case 'save_settings':
                return $this->saveSettings($request);
            case 'add_egg':
                return $this->addEgg($request);
            case 'toggle_egg':
                return $this->toggleEgg($request);
            case 'remove_egg':
                return $this->removeEgg($request);
            case 'search_users':
                return $this->searchUsers($request);
            case 'add_user_limit':
                return $this->addUserLimit($request);
            case 'update_user_limit':
                return $this->updateUserLimit($request);
            case 'remove_user_limit':
                return $this->removeUserLimit($request);
            default:
                return redirect('/admin/extensions/freeservers')->with('error', T::t('msg_error_unknown'));
        }
    }

    private function saveSettings(Request $request): RedirectResponse
    {
        $enabled = $request->input('enabled', 0) == '1' ? 1 : 0;

        // Reset translation cache so new language takes effect immediately
        T::reset();
        
        DB::table('freeservers_settings')->update([
            'enabled' => $enabled,
            'max_servers_per_user' => (int) $request->input('max_servers_per_user', 1),
            'default_memory' => (int) $request->input('default_memory', 1024),
            'default_disk' => (int) $request->input('default_disk', 5120),
            'default_cpu' => (int) $request->input('default_cpu', 100),
            'default_swap' => (int) $request->input('default_swap', 0),
            'default_io' => (int) $request->input('default_io', 500),
            'default_databases' => (int) $request->input('default_databases', 0),
            'default_allocations' => (int) $request->input('default_allocations', 1),
            'default_backups' => (int) $request->input('default_backups', 0),
            'allowed_nodes' => json_encode($request->input('allowed_nodes', [])),
            'server_description' => $request->input('server_description', 'Free Server created via Free Servers Extension'),
            'display_unit' => $request->input('display_unit', 'MB'),
            'language' => $request->input('language', 'en'),
            'expiration_days' => (int) $request->input('expiration_days', 0),
            'extension_days' => (int) $request->input('extension_days', 30),
            'allow_extension' => (bool) $request->input('allow_extension', true),
            'enable_stats' => (bool) $request->input('enable_stats', true),
            'discord_webhook_url' => $request->input('discord_webhook_url', null),
            'discord_notify_create' => (bool) $request->input('discord_notify_create', false),
            'discord_notify_delete' => (bool) $request->input('discord_notify_delete', false),
            'discord_notify_expire' => (bool) $request->input('discord_notify_expire', false),
            'updated_at' => now(),
        ]);

        return redirect('/admin/extensions/freeservers')->with('success', T::t('msg_success_save'));
    }

    private function addEgg(Request $request): RedirectResponse
    {
        $eggId = $request->input('egg_id');
        
        if (!$eggId) {
            return redirect('/admin/extensions/freeservers')->with('error', T::t('msg_error_select_egg'));
        }
        
        $exists = DB::table('freeservers_allowed_eggs')->where('egg_id', $eggId)->exists();
        if ($exists) {
            return redirect('/admin/extensions/freeservers')->with('error', T::t('msg_error_egg_exists'));
        }

        DB::table('freeservers_allowed_eggs')->insert([
            'egg_id' => $eggId,
            'custom_name' => $request->input('custom_name') ?: null,
            'custom_description' => $request->input('custom_description') ?: null,
            'custom_memory' => $request->input('custom_memory') ? (int) $request->input('custom_memory') : null,
            'custom_disk' => $request->input('custom_disk') ? (int) $request->input('custom_disk') : null,
            'custom_cpu' => $request->input('custom_cpu') ? (int) $request->input('custom_cpu') : null,
            'enabled' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/admin/extensions/freeservers')->with('success', T::t('msg_success_egg_add'));
    }

    private function toggleEgg(Request $request): RedirectResponse
    {
        $id = $request->input('egg_id');
        $egg = DB::table('freeservers_allowed_eggs')->where('id', $id)->first();
        
        if ($egg) {
            DB::table('freeservers_allowed_eggs')->where('id', $id)->update([
                'enabled' => $egg->enabled ? 0 : 1,
                'updated_at' => now(),
            ]);
        }

        return redirect('/admin/extensions/freeservers')->with('success', T::t('msg_success_status'));
    }

    private function removeEgg(Request $request): RedirectResponse
    {
        DB::table('freeservers_allowed_eggs')->where('id', $request->input('egg_id'))->delete();
        return redirect('/admin/extensions/freeservers')->with('success', T::t('msg_success_egg_remove'));
    }

    private function searchUsers(Request $request): JsonResponse
    {
        $query = $request->input('query', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where('username', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get(['id', 'username', 'email']);

        $users = $users->map(function ($user) {
            $serverCount = DB::table('freeservers_servers')
                ->where('user_id', $user->id)
                ->count();
            $hasCustomLimit = DB::table('freeservers_user_limits')
                ->where('user_id', $user->id)
                ->exists();

            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'current_servers' => $serverCount,
                'has_custom_limit' => $hasCustomLimit,
            ];
        });

        return response()->json($users);
    }

    private function addUserLimit(Request $request): RedirectResponse
    {
        $userId = $request->input('user_id');
        $maxServers = (int) $request->input('max_servers', 0);

        if (!$userId) {
            return redirect('/admin/extensions/freeservers')->with('error', T::t('msg_error_select_user'));
        }

        if ($maxServers < 0) {
            return redirect('/admin/extensions/freeservers')->with('error', T::t('msg_error_negative'));
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect('/admin/extensions/freeservers')->with('error', T::t('msg_error_user_not_found'));
        }

        $exists = DB::table('freeservers_user_limits')->where('user_id', $userId)->exists();
        if ($exists) {
            return redirect('/admin/extensions/freeservers')->with('error', T::t('msg_error_limit_exists'));
        }

        DB::table('freeservers_user_limits')->insert([
            'user_id' => $userId,
            'max_servers' => $maxServers,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/admin/extensions/freeservers')->with('success', T::t('msg_success_limit_set', $user->username));
    }

    private function updateUserLimit(Request $request): RedirectResponse
    {
        $limitId = $request->input('limit_id');
        $maxServers = (int) $request->input('max_servers', 0);

        if ($maxServers < 0) {
            return redirect('/admin/extensions/freeservers')->with('error', T::t('msg_error_negative'));
        }

        $limit = DB::table('freeservers_user_limits')->where('id', $limitId)->first();
        if (!$limit) {
            return redirect('/admin/extensions/freeservers')->with('error', T::t('msg_error_limit_not_found'));
        }

        DB::table('freeservers_user_limits')->where('id', $limitId)->update([
            'max_servers' => $maxServers,
            'updated_at' => now(),
        ]);

        return redirect('/admin/extensions/freeservers')->with('success', T::t('msg_success_limit_update'));
    }

    private function removeUserLimit(Request $request): RedirectResponse
    {
        $limitId = $request->input('limit_id');

        $limit = DB::table('freeservers_user_limits')->where('id', $limitId)->first();
        if ($limit) {
            $user = User::find($limit->user_id);
            DB::table('freeservers_user_limits')->where('id', $limitId)->delete();
            $username = $user ? $user->username : 'User';
            return redirect('/admin/extensions/freeservers')->with('success', T::t('msg_success_limit_remove', $username));
        }

        return redirect('/admin/extensions/freeservers')->with('error', T::t('msg_error_limit_not_found'));
    }

    private function stats(): array
    {
        $settings = DB::table('freeservers_settings')->first();
        
        if (!$settings || !$settings->enable_stats) {
            return [];
        }

        $totalServers = DB::table('freeservers_servers')->count();
        
        $activeServers = DB::table('freeservers_servers')
            ->join('servers', 'freeservers_servers.server_id', '=', 'servers.id')
            ->whereIn('servers.status', ['running', 'starting'])
            ->count();

        $today = DB::table('freeservers_servers')
            ->whereDate('created_at', today())
            ->count();

        $last7Days = DB::table('freeservers_servers')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $expiringSoon = DB::table('freeservers_servers')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays(7)])
            ->count();

        $topUsers = DB::table('freeservers_servers')
            ->select('user_id', DB::raw('COUNT(*) as server_count'))
            ->join('users', 'freeservers_servers.user_id', '=', 'users.id')
            ->groupBy('user_id')
            ->orderBy('server_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $user = User::find($row->user_id);
                return [
                    'username' => $user ? $user->username : 'Unknown',
                    'count' => $row->server_count
                ];
            });

        $resources = DB::table('freeservers_servers')
            ->join('servers', 'freeservers_servers.server_id', '=', 'servers.id')
            ->select(
                DB::raw('SUM(servers.memory) as total_memory'),
                DB::raw('SUM(servers.disk) as total_disk'),
                DB::raw('AVG(servers.cpu) as avg_cpu')
            )
            ->first();

        return [
            'total_servers' => $totalServers,
            'active_servers' => $activeServers,
            'inactive_servers' => $totalServers - $activeServers,
            'today' => $today,
            'last_7_days' => $last7Days,
            'expiring_soon' => $expiringSoon,
            'top_users' => $topUsers,
            'resources' => [
                'total_memory_mb' => round($resources->total_memory ?? 0),
                'total_memory_gb' => round(($resources->total_memory ?? 0) / 1024, 2),
                'total_disk_mb' => round($resources->total_disk ?? 0),
                'total_disk_gb' => round(($resources->total_disk ?? 0) / 1024, 2),
                'avg_cpu' => round($resources->avg_cpu ?? 0),
            ],
        ];
    }
}

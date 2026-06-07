<?php

namespace Pterodactyl\BlueprintFramework\Extensions\freeservers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Models\Egg;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\Allocation;
use Pterodactyl\Services\Servers\ServerCreationService;
use Pterodactyl\BlueprintFramework\Extensions\freeservers\TranslationHelper as T;

class FreeServersController extends Controller
{
    public function __construct(
        private ServerCreationService $creationService
    ) {}

    /**
     * Get the effective server limit for a user.
     * Returns custom limit if set, otherwise returns global limit.
     */
    private function getEffectiveLimit(int $userId, int $globalLimit): int
    {
        $userLimit = DB::table('freeservers_user_limits')
            ->where('user_id', $userId)
            ->first();

        return $userLimit ? $userLimit->max_servers : $globalLimit;
    }

    public function index(Request $request): JsonResponse
    {
        if (!Schema::hasTable('freeservers_settings')) {
            return response()->json([
                'enabled' => false,
                'message' => T::t('api_tables_missing')
            ]);
        }

        $user = $request->user();
        $settings = DB::table('freeservers_settings')->first();

        if (!$settings || $settings->enabled != 1) {
            return response()->json([
                'enabled' => false,
                'message' => T::t('api_disabled')
            ]);
        }

        $userFreeServersCount = DB::table('freeservers_servers')
            ->where('user_id', $user->id)
            ->count();

        $effectiveLimit = $this->getEffectiveLimit($user->id, $settings->max_servers_per_user);

        $allowedEggs = DB::table('freeservers_allowed_eggs')
            ->where('freeservers_allowed_eggs.enabled', 1)
            ->join('eggs', 'freeservers_allowed_eggs.egg_id', '=', 'eggs.id')
            ->join('nests', 'eggs.nest_id', '=', 'nests.id')
            ->select(
                'freeservers_allowed_eggs.id',
                'freeservers_allowed_eggs.egg_id',
                'freeservers_allowed_eggs.custom_name',
                'freeservers_allowed_eggs.custom_description',
                'freeservers_allowed_eggs.custom_memory',
                'freeservers_allowed_eggs.custom_disk',
                'freeservers_allowed_eggs.custom_cpu',
                'eggs.name as egg_name',
                'eggs.description as egg_description',
                'nests.name as nest_name'
            )
            ->get()
            ->map(function ($egg) use ($settings) {
                return [
                    'id' => $egg->id,
                    'egg_id' => $egg->egg_id,
                    'name' => $egg->custom_name ?? $egg->egg_name,
                    'description' => $egg->custom_description ?? $egg->egg_description,
                    'nest_name' => $egg->nest_name,
                    'memory' => $egg->custom_memory ?? $settings->default_memory,
                    'disk' => $egg->custom_disk ?? $settings->default_disk,
                    'cpu' => $egg->custom_cpu ?? $settings->default_cpu,
                ];
            });

        $allowedNodeIds = json_decode($settings->allowed_nodes ?? '[]', true) ?? [];
        
        $nodesQuery = Node::query()
            ->where('public', true)
            ->whereHas('allocations', function ($query) {
                $query->whereNull('server_id');
            });

        if (!empty($allowedNodeIds)) {
            $nodesQuery->whereIn('id', $allowedNodeIds);
        }

        $nodes = $nodesQuery->with('location')->get()->map(function ($node) {
            return [
                'id' => $node->id,
                'name' => $node->name,
                'location' => $node->location->short ?? 'Unknown',
            ];
        });

        return response()->json([
            'enabled' => true,
            'max_servers' => $effectiveLimit,
            'current_servers' => $userFreeServersCount,
            'can_create' => $userFreeServersCount < $effectiveLimit,
            'remaining' => max(0, $effectiveLimit - $userFreeServersCount),
            'eggs' => $allowedEggs,
            'nodes' => $nodes,
            'display_unit' => $settings->display_unit ?? 'MB',
            'language' => $settings->language ?? 'en',
            'resources' => [
                'memory' => $settings->default_memory,
                'disk' => $settings->default_disk,
                'cpu' => $settings->default_cpu,
                'swap' => $settings->default_swap,
                'io' => $settings->default_io,
                'databases' => $settings->default_databases,
                'allocations' => $settings->default_allocations,
                'backups' => $settings->default_backups,
            ],
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $user = $request->user();
        $settings = DB::table('freeservers_settings')->first();

        if (!$settings || $settings->enabled != 1) {
            return response()->json([
                'success' => false,
                'message' => T::t('api_disabled')
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|min:3|max:40',
            'egg_id' => 'required|integer',
            'node_id' => 'required|integer',
        ]);

        $allowedEgg = DB::table('freeservers_allowed_eggs')
            ->where('id', $validated['egg_id'])
            ->where('enabled', 1)
            ->first();

        if (!$allowedEgg) {
            return response()->json([
                'success' => false,
                'message' => T::t('api_egg_not_available')
            ], 403);
        }

        $allowedNodeIds = json_decode($settings->allowed_nodes ?? '[]', true) ?? [];
        if (!empty($allowedNodeIds) && !in_array((int) $validated['node_id'], $allowedNodeIds)) {
            return response()->json([
                'success' => false,
                'message' => T::t('api_node_not_available')
            ], 403);
        }

        $egg = Egg::with('variables')->findOrFail($allowedEgg->egg_id);
        $node = Node::findOrFail($validated['node_id']);

        // Check server limit (quick check without long-held locks)
        $userFreeServersCount = DB::table('freeservers_servers')
            ->where('user_id', $user->id)
            ->count();

        $effectiveLimit = $this->getEffectiveLimit($user->id, $settings->max_servers_per_user);

        if ($userFreeServersCount >= $effectiveLimit) {
            return response()->json([
                'success' => false,
                'message' => T::t('api_limit_reached')
            ], 403);
        }

        // Find a free allocation on the selected node
        $allocation = Allocation::where('node_id', $node->id)
            ->whereNull('server_id')
            ->first();

        if (!$allocation) {
            return response()->json([
                'success' => false,
                'message' => T::t('api_no_ports')
            ], 503);
        }

        // Prepare resource values with explicit type casting
        $memory = (int) ($allowedEgg->custom_memory ?? $settings->default_memory);
        $disk = (int) ($allowedEgg->custom_disk ?? $settings->default_disk);
        $cpu = (int) ($allowedEgg->custom_cpu ?? $settings->default_cpu);

        // Build environment with null-safe default values
        $environment = [];
        foreach ($egg->variables as $variable) {
            $environment[$variable->env_variable] = $variable->default_value ?? '';
        }

        // Resolve Docker image
        $dockerImages = $egg->docker_images;
        if (is_string($dockerImages)) {
            $dockerImages = json_decode($dockerImages, true);
        }
        if (is_object($dockerImages)) {
            $dockerImages = (array) $dockerImages;
        }
        $dockerImage = is_array($dockerImages) && !empty($dockerImages) ? array_values($dockerImages)[0] : '';

        if (empty($dockerImage)) {
            return response()->json([
                'success' => false,
                'message' => T::t('api_no_docker')
            ], 422);
        }

        // Create server - let ServerCreationService manage its own transaction and Wings communication
        try {
            $server = $this->creationService->handle([
                'name' => $validated['name'],
                'description' => $settings->server_description ?? 'Free Server created via Free Servers Extension',
                'owner_id' => $user->id,
                'egg_id' => $egg->id,
                'nest_id' => $egg->nest_id,
                'node_id' => $node->id,
                'allocation_id' => $allocation->id,
                'allocation_additional' => [],
                'environment' => $environment,
                'memory' => $memory,
                'swap' => (int) ($settings->default_swap ?? 0),
                'disk' => $disk,
                'cpu' => $cpu,
                'threads' => null,
                'io' => (int) ($settings->default_io ?? 500),
                'database_limit' => (int) ($settings->default_databases ?? 0),
                'allocation_limit' => (int) ($settings->default_allocations ?? 1),
                'backup_limit' => (int) ($settings->default_backups ?? 0),
                'image' => $dockerImage,
                'startup' => $egg->startup,
                'start_on_completion' => false,
                'skip_scripts' => false,
                'oom_disabled' => true,
            ]);

            // Set expiration if enabled
            $expiresAt = null;
            if ($settings->expiration_days > 0) {
                $expiresAt = now()->addDays($settings->expiration_days);
            }

            DB::table('freeservers_servers')->insert([
                'user_id' => $user->id,
                'server_id' => $server->id,
                'egg_id' => $egg->id,
                'expires_at' => $expiresAt,
                'last_extended_at' => null,
                'extension_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => T::t('api_success_create'),
                'server' => [
                    'id' => $server->id,
                    'uuid' => $server->uuid,
                    'name' => $server->name,
                ],
            ]);
        } catch (\Exception $e) {
            // If allocation was already taken by a concurrent request, suggest retrying
            if (str_contains($e->getMessage(), 'allocation') || str_contains($e->getMessage(), 'port')) {
                return response()->json([
                    'success' => false,
                    'message' => T::t('api_port_taken')
                ], 409);
            }

            return response()->json([
                'success' => false,
                'message' => T::t('api_error_create', $e->getMessage())
            ], 500);
        }
    }

    public function myServers(Request $request): JsonResponse
    {
        $user = $request->user();

        $freeServers = DB::table('freeservers_servers')
            ->where('freeservers_servers.user_id', $user->id)
            ->join('servers', 'freeservers_servers.server_id', '=', 'servers.id')
            ->join('eggs', 'freeservers_servers.egg_id', '=', 'eggs.id')
            ->select(
                'freeservers_servers.*',
                'servers.name as server_name',
                'servers.uuid as server_uuid',
                'servers.status',
                'eggs.name as egg_name'
            )
            ->get();

        return response()->json([
            'servers' => $freeServers,
        ]);
    }

    public function extend(Request $request): JsonResponse
    {
        $user = $request->user();
        $settings = DB::table('freeservers_settings')->first();

        if (!$settings || !$settings->allow_extension) {
            return response()->json([
                'success' => false,
                'message' => T::t('api_extension_disabled')
            ], 403);
        }

        $validated = $request->validate([
            'server_id' => 'required|integer',
        ]);

        $freeServer = DB::table('freeservers_servers')
            ->where('user_id', $user->id)
            ->where('server_id', $validated['server_id'])
            ->first();

        if (!$freeServer) {
            return response()->json([
                'success' => false,
                'message' => T::t('api_server_not_found')
            ], 404);
        }

        // Calculate new expiration date
        $currentExpiration = $freeServer->expires_at ? \Carbon\Carbon::parse($freeServer->expires_at) : now();
        $newExpiration = $currentExpiration->addDays($settings->extension_days);

        DB::table('freeservers_servers')
            ->where('id', $freeServer->id)
            ->update([
                'expires_at' => $newExpiration,
                'last_extended_at' => now(),
                'extension_count' => ($freeServer->extension_count ?? 0) + 1,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => T::t('api_success_extend'),
            'new_expiration' => $newExpiration->toIso8601String(),
        ]);
    }
}

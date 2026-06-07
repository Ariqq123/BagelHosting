---
name: pterodactyl-live-ops
description: Use when working on a live Pterodactyl panel, Blueprint extension, Wings/server recovery, panel database issue, frontend asset build, Laravel cache/storage permission error, or any production panel debugging where tests, DB writes, route caches, or generated assets could affect users.
---

# Pterodactyl Live Ops

## Core Rule

Treat the panel as production until proven otherwise. Prefer read-only investigation first. Do not run tests, migrations, seeders, destructive commands, or DB writes without a fresh DB backup and a concrete reason.

## Production Guardrails

- Before any panel DB write: create a timestamped SQL backup and state its path.
- Never run PHPUnit/Pest against an unknown Pterodactyl environment; feature tests can wipe the live `panel` DB via migrations.
- Use `php artisan tinker --execute` only for read-only inspection unless a backup already exists for the requested write.
- Do not print secrets from configs, webhooks, tokens, `.env`, server files, or logs.
- Run Laravel cache/route clearing as the web user when possible: `sudo -u www-data php artisan optimize:clear`.

## Fast Diagnosis

For a 500:

1. Read the current Laravel log first:
   `tail -220 storage/logs/laravel-$(date +%F).log | rg -i 'production.ERROR|permission denied|exception|file_put_contents|freeservers|subdomain'`
2. If it is `file_put_contents(...storage/framework/cache/data...): Permission denied`, fix ownership and modes:
   `chown -R www-data:www-data storage bootstrap/cache`
   `find storage bootstrap/cache -type d -exec chmod 775 {} +`
   `find storage bootstrap/cache -type f -exec chmod 664 {} +`
   `sudo -u www-data php artisan optimize:clear`
3. Verify as `www-data`:
   `sudo -u www-data sh -c 'mkdir -p storage/framework/cache/data/_probe && printf ok > storage/framework/cache/data/_probe/write-test && rm -f storage/framework/cache/data/_probe/write-test'`

For a Blueprint extension page that loads but shows unavailable/404:

1. Check backend route registration:
   `php artisan route:list -vv | rg 'api/client/extensions|admin/extensions|freeservers|blueprint'`
2. Check generated frontend source actually imports/renders the component.
3. Check the active router, not only Blueprint's generated router. Arix/Pterodactyl themes may use `resources/scripts/routers/DashboardRouter.tsx` instead of `resources/scripts/blueprint/extends/routers/DashboardRouter.tsx`.
4. Rebuild assets with the OpenSSL flag if webpack/css-loader fails:
   `NODE_OPTIONS=--openssl-legacy-provider yarn run build:production`
5. Confirm live HTML references the new bundle:
   `curl -sS https://panel.example/account/path | rg 'assets/bundle|Page Not Found|Not Found'`

## Blueprint Route Lessons

- Blueprint client routes live under `routes/blueprint/client.php`, scanning `routes/blueprint/client/*.php`.
- Symlinked extension route files can fail to register reliably; if route-list misses the route, replace the symlink with a real route file or add an explicit include.
- Client extension routes should appear as:
  `api/client/extensions/<extension>`
- After route changes, use `php artisan route:list -vv`; short `route:list --path=...` output can be misleading.

## FreeServers Specific Checks

When FreeServers says unavailable:

- Inspect the API/data conditions in the React component before guessing.
- `FreeServersPageContent` shows unavailable when `data` is missing, `enabled` is false, or `max_servers === 0`.
- Check per-user custom limits; a custom limit of `0` intentionally blocks the user even if they have no free servers:
  `freeservers_user_limits.max_servers = 0`
- Check global settings, allowed eggs, public nodes, and free allocations.
- Existing tracked servers can drift: panel DB may contain a server that Wings returns 404 for. Do not assume the extension create flow caused it.

## Server/Volume Recovery

- Compare `/var/lib/pterodactyl/volumes` UUIDs with `servers.uuid`.
- Preserve old UUIDs where possible; users may identify servers by old UUID short IDs.
- Register missing recovered servers with Wings after DB recovery; verify containers, ports, and logs.
- For Minecraft version mismatch, inspect `server.jar`, startup vars, `server.properties`, `world/playerdata`, and recent logs before changing files.

## Common Mistakes

- Running artisan/cache commands as `root` and recreating root-owned cache files.
- Fixing generated Blueprint files but not rebuilding `public/assets`.
- Patching Blueprint's generated router while the active theme router is elsewhere.
- Assuming a browser-visible "404" is a web-server 404; direct URL may return `200` while React renders NotFound.
- Treating "unavailable" as "already created a server"; it may be missing API data or a custom limit of `0`.

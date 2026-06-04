# Subdomains Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Cloudflare-backed admin domain config and per-server user subdomain management.

**Architecture:** Store admin Cloudflare domain configs separately from user-created DNS records. Keep Cloudflare HTTP calls in `CloudflareDnsService`, expose thin client/admin controllers, and mirror existing database/allocation server-feature patterns.

**Tech Stack:** Laravel 11, Eloquent, Fractal transformers, Laravel HTTP client, Blade admin UI, React 16/TypeScript panel UI, Jest/PHPUnit.

---

## File Map

- Create `database/migrations/2026_06_04_000002_create_subdomain_domains_table.php`: Cloudflare domain config table.
- Create `database/migrations/2026_06_04_000003_create_subdomains_table.php`: per-server DNS records.
- Create `database/migrations/2026_06_04_000004_add_subdomain_limit_to_servers_table.php`: feature limit.
- Modify `app/Models/Server.php`: `subdomain_limit` validation/casts/relation.
- Create `app/Models/SubdomainDomain.php` and `app/Models/Subdomain.php`: model rules/relations.
- Modify `app/Models/Permission.php`: `subdomain.read/create/delete`.
- Create `app/Services/Subdomains/CloudflareDnsService.php`: Cloudflare create/delete API wrapper.
- Create `app/Services/Subdomains/SubdomainManagementService.php`: orchestration, limits, Cloudflare + DB writes.
- Create client API requests/controllers/transformer under `app/Http/.../Subdomains` and `app/Transformers/Api/Client/SubdomainTransformer.php`.
- Modify `routes/api-client.php`: `/servers/{server}/subdomains` routes.
- Create admin controller/request/views under `app/Http/Controllers/Admin/SubdomainDomainController.php`, `app/Http/Requests/Admin/SubdomainDomainFormRequest.php`, `resources/views/admin/subdomains/*`.
- Modify `routes/admin.php` and `resources/views/layouts/admin.blade.php`: admin nav/routes.
- Modify server creation/build request/service/views for `subdomain_limit`.
- Create React API files under `resources/scripts/api/server/subdomains/*`.
- Create React UI under `resources/scripts/components/server/subdomains/*`.
- Modify `resources/scripts/routers/routes.ts`: add server tab.
- Create tests under `tests/Unit/Services/Subdomains/*` and `tests/Integration/Api/Client/Servers/Subdomains/*`.

---

### Task 1: Database Schema And Models

**Files:**
- Create: `database/migrations/2026_06_04_000002_create_subdomain_domains_table.php`
- Create: `database/migrations/2026_06_04_000003_create_subdomains_table.php`
- Create: `database/migrations/2026_06_04_000004_add_subdomain_limit_to_servers_table.php`
- Create: `app/Models/SubdomainDomain.php`
- Create: `app/Models/Subdomain.php`
- Modify: `app/Models/Server.php`

- [ ] **Step 1: Add migrations**

Create `subdomain_domains` with unique `name`, `cloudflare_zone_id`, encrypted-token storage column `cloudflare_token`, JSON `allowed_record_types`, nullable `cname_target`, booleans `proxied` and `enabled`, timestamps.

Create `subdomains` with `server_id`, nullable `user_id`, `subdomain_domain_id`, `name`, unique `fqdn`, `type`, `content`, `proxied`, nullable `cloudflare_record_id`, `status`, nullable `error_message`, timestamps, FK cascade to server/domain/user where appropriate.

Add nullable unsigned integer `subdomain_limit` to `servers` after `backup_limit`, default `0`.

- [ ] **Step 2: Add models**

`SubdomainDomain`: table `subdomain_domains`, fillable `name`, `cloudflare_zone_id`, `cloudflare_token`, `allowed_record_types`, `cname_target`, `proxied`, `enabled`; casts token as `encrypted`, allowed types as `array`, booleans as `boolean`; has many `subdomains`.

`Subdomain`: table `subdomains`, resource name `subdomain`, fillable record fields, casts IDs/booleans, belongs to `server`, `user`, and `domain`.

- [ ] **Step 3: Wire server relation and rules**

In `Server.php`, add docblock entries for `subdomain_limit` and `subdomains`; add validation rule `'subdomain_limit' => 'present|nullable|integer|min:0'`; add cast `'subdomain_limit' => 'integer'`; add `subdomains(): HasMany` returning `$this->hasMany(Subdomain::class)`.

- [ ] **Step 4: Verify migrations/models**

Run: `php artisan migrate --pretend`
Expected: SQL for all three new migrations, no PHP fatal errors.

- [ ] **Step 5: Commit**

Run: `git add database/migrations app/Models && git commit -m "feat: add subdomain data models"`

---

### Task 2: Permissions And Server Limits

**Files:**
- Modify: `app/Models/Permission.php`
- Modify: `app/Http/Requests/Api/Application/Servers/StoreServerRequest.php`
- Modify: `app/Http/Requests/Api/Application/Servers/UpdateServerBuildConfigurationRequest.php`
- Modify: `app/Services/Servers/ServerCreationService.php`
- Modify: `app/Services/Servers/BuildModificationService.php`
- Modify: `resources/views/admin/servers/new.blade.php`
- Modify: `resources/views/admin/servers/view/build.blade.php`

- [ ] **Step 1: Add permission constants and group**

Add constants `ACTION_SUBDOMAIN_READ`, `ACTION_SUBDOMAIN_CREATE`, `ACTION_SUBDOMAIN_DELETE`. Add permission group `subdomain` with read/create/delete descriptions matching the existing `database` and `allocation` wording style.

- [ ] **Step 2: Add application API limit support**

In store/update server requests, add `feature_limits.subdomains` using `Server` rule `subdomain_limit`; include `subdomain_limit` in normalized validated data and custom attributes.

- [ ] **Step 3: Persist limit**

In `ServerCreationService::createModel`, add `'subdomain_limit' => Arr::get($data, 'subdomain_limit') ?? 0`. In `BuildModificationService`, add `'subdomain_limit' => Arr::get($data, 'subdomain_limit', 0) ?? 0` to the forced fill payload.

- [ ] **Step 4: Add admin form fields**

Add a `Subdomain Limit` input beside database/allocation/backup limits in create and build Blade views, named `subdomain_limit`, defaulting to `0` on create and `$server->subdomain_limit` on edit.

- [ ] **Step 5: Verify validation**

Run: `php artisan route:list --path=api/application/servers`
Expected: route list renders without request/model fatal errors.

- [ ] **Step 6: Commit**

Run: `git add app resources/views/admin/servers && git commit -m "feat: add subdomain permissions and limits"`

---

### Task 3: Cloudflare And Subdomain Services

**Files:**
- Create: `app/Services/Subdomains/CloudflareDnsService.php`
- Create: `app/Services/Subdomains/SubdomainManagementService.php`
- Create: `tests/Unit/Services/Subdomains/CloudflareDnsServiceTest.php`

- [ ] **Step 1: Write Cloudflare service tests**

Test `createRecord()` sends `POST https://api.cloudflare.com/client/v4/zones/{zone}/dns_records` with bearer token and returns response `id`. Test `deleteRecord()` sends `DELETE .../dns_records/{id}`. Test non-success Cloudflare JSON throws `DisplayException` containing Cloudflare error message.

- [ ] **Step 2: Implement `CloudflareDnsService`**

Use `Http::withToken($domain->cloudflare_token)->acceptJson()->asJson()`. `createRecord(SubdomainDomain $domain, string $type, string $fqdn, string $content, bool $proxied): string` posts `type`, `name`, `content`, `proxied`, `ttl => 1`. `deleteRecord(SubdomainDomain $domain, string $recordId): void` deletes. Parse `success`, `result.id`, and `errors.*.message`.

- [ ] **Step 3: Implement management service**

`create(Server $server, User $user, SubdomainDomain $domain, string $name, string $type): Subdomain` locks server subdomains, enforces `$server->subdomain_limit`, builds lowercase FQDN, chooses content from `$server->allocation->ip` for `A` or `$domain->cname_target` for `CNAME`, calls Cloudflare, creates DB row `active`.

`delete(Subdomain $subdomain): void` calls Cloudflare if `cloudflare_record_id` exists, deletes row on success, and on failure stores `status=error` plus `error_message` before rethrowing.

- [ ] **Step 4: Run tests**

Run: `./vendor/bin/phpunit tests/Unit/Services/Subdomains/CloudflareDnsServiceTest.php`
Expected: PASS.

- [ ] **Step 5: Commit**

Run: `git add app/Services tests/Unit && git commit -m "feat: add Cloudflare subdomain service"`

---

### Task 4: Client API

**Files:**
- Create: `app/Http/Controllers/Api/Client/Servers/SubdomainController.php`
- Create: `app/Http/Requests/Api/Client/Servers/Subdomains/GetSubdomainsRequest.php`
- Create: `app/Http/Requests/Api/Client/Servers/Subdomains/StoreSubdomainRequest.php`
- Create: `app/Http/Requests/Api/Client/Servers/Subdomains/DeleteSubdomainRequest.php`
- Create: `app/Transformers/Api/Client/SubdomainTransformer.php`
- Modify: `routes/api-client.php`
- Modify: `app/Http/Middleware/Api/Client/Server/ResourceBelongsToServer.php`

- [ ] **Step 1: Add request classes**

`GetSubdomainsRequest` returns `Permission::ACTION_SUBDOMAIN_READ`. `DeleteSubdomainRequest` returns `Permission::ACTION_SUBDOMAIN_DELETE`. `StoreSubdomainRequest` returns `Permission::ACTION_SUBDOMAIN_CREATE` and validates `name` with regex `/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/`, `domain_id` exists/enabled, `type` in `A,CNAME`, unique FQDN via after-validation check.

- [ ] **Step 2: Add transformer**

Return `id`, `name`, `fqdn`, `type`, `content`, `proxied`, `status`, `error_message`, domain summary, `created_at`, `updated_at`.

- [ ] **Step 3: Add controller**

`index()` returns server subdomains and enabled domains metadata. `store()` calls `SubdomainManagementService::create()` and logs `server:subdomain.create`. `delete()` calls service delete and logs `server:subdomain.delete`, returns HTTP 204.

- [ ] **Step 4: Add routes/binding guard**

In `routes/api-client.php`, add `/subdomains` group near databases/network. In `ResourceBelongsToServer`, add `Subdomain::class` check against `server_id`.

- [ ] **Step 5: Run route smoke**

Run: `php artisan route:list --path=api/client/servers/{server}/subdomains`
Expected: GET, POST, DELETE routes render.

- [ ] **Step 6: Commit**

Run: `git add app/Http app/Transformers routes/api-client.php && git commit -m "feat: add subdomain client API"`

---

### Task 5: Admin Domain Management

**Files:**
- Create: `app/Http/Controllers/Admin/SubdomainDomainController.php`
- Create: `app/Http/Requests/Admin/SubdomainDomainFormRequest.php`
- Create: `resources/views/admin/subdomains/index.blade.php`
- Create: `resources/views/admin/subdomains/form.blade.php`
- Modify: `routes/admin.php`
- Modify: `resources/views/layouts/admin.blade.php`

- [ ] **Step 1: Add admin request**

Validate `name` as required domain-like string unique by model ID, `cloudflare_zone_id` required string max 191, `cloudflare_token` required on create and nullable on update, `allowed_record_types` required array with values `A`/`CNAME`, `cname_target` required when `CNAME` allowed, `proxied` boolean, `enabled` boolean.

- [ ] **Step 2: Add controller**

Implement `index`, `create`, `store`, `edit`, `update`, `delete`. On update, keep existing encrypted token when token input is blank. Use alerts and redirects matching other admin controllers.

- [ ] **Step 3: Add Blade UI**

Index table: name, zone ID, allowed types, proxied, enabled, record count, edit/delete. Form: fields for domain, zone ID, token, allowed types checkboxes, CNAME target, proxied, enabled.

- [ ] **Step 4: Wire routes/nav**

Add `admin/subdomains` routes named `admin.subdomains.*`. Add sidebar item under Management with `globe` icon and active route check.

- [ ] **Step 5: Verify admin routes**

Run: `php artisan route:list --path=admin/subdomains`
Expected: index/create/store/edit/update/delete routes render.

- [ ] **Step 6: Commit**

Run: `git add app/Http/Controllers/Admin app/Http/Requests/Admin resources/views/admin/subdomains resources/views/layouts/admin.blade.php routes/admin.php && git commit -m "feat: add subdomain admin management"`

---

### Task 6: Server Panel UI

**Files:**
- Create: `resources/scripts/api/server/subdomains/getServerSubdomains.ts`
- Create: `resources/scripts/api/server/subdomains/createServerSubdomain.ts`
- Create: `resources/scripts/api/server/subdomains/deleteServerSubdomain.ts`
- Create: `resources/scripts/components/server/subdomains/SubdomainsContainer.tsx`
- Create: `resources/scripts/components/server/subdomains/CreateSubdomainButton.tsx`
- Create: `resources/scripts/components/server/subdomains/SubdomainRow.tsx`
- Modify: `resources/scripts/routers/routes.ts`
- Modify: `resources/scripts/api/server/getServer.ts` or existing server type source for `featureLimits.subdomains`

- [ ] **Step 1: Add TS API types**

Define `ServerSubdomain`, `SubdomainDomainOption`, `ServerSubdomainResponse`. Map Fractal attributes from `/api/client/servers/${uuid}/subdomains`.

- [ ] **Step 2: Add create/delete API helpers**

POST `{ name, domain_id, type }` to subdomains endpoint and map returned record. DELETE by record ID.

- [ ] **Step 3: Add UI container**

Follow `DatabasesContainer` shape: load records, show limit text, show create button only under `Can action={'subdomain.create'}` when limit allows, render table with FQDN/type/content/status/actions.

- [ ] **Step 4: Add create modal/button**

Formik form with prefix input, domain select, type select filtered by allowed types, disabled when no enabled domains. Submit calls API, appends row, closes modal, flashes errors under key `subdomains:create`.

- [ ] **Step 5: Add row delete action**

Use confirmation modal/menu pattern from database/backup rows. Delete calls API, removes row, surfaces error through `subdomains` flash key.

- [ ] **Step 6: Add route tab**

Import `SubdomainsContainer` and add management route `{ path: '/subdomains', permission: 'subdomain.*', name: 'subdomains', icon: GlobeIcon, component: SubdomainsContainer }`.

- [ ] **Step 7: Verify frontend**

Run: `yarn run tsc`
Expected: no TypeScript errors.

Run: `yarn run build`
Expected: webpack build succeeds.

- [ ] **Step 8: Commit**

Run: `git add resources/scripts && git commit -m "feat: add subdomain server UI"`

---

### Task 7: Cleanup, Deletion, And Full Verification

**Files:**
- Modify: `app/Services/Servers/ServerDeletionService.php`
- Create or modify: backend tests from previous tasks

- [ ] **Step 1: Add deletion cleanup**

Before/inside server deletion transaction, iterate `$server->subdomains` and call `SubdomainManagementService::delete()` for each. If Cloudflare fails, keep the row marked `error` and abort deletion with `DisplayException`.

- [ ] **Step 2: Add integration tests**

Test create rejects limit `0`, rejects duplicate FQDN, rejects disallowed type, creates record when service returns Cloudflare ID, delete removes server-owned record, delete rejects record from another server.

- [ ] **Step 3: Run backend verification**

Run: `./vendor/bin/phpunit tests/Unit/Services/Subdomains tests/Integration/Api/Client/Servers/Subdomains`
Expected: PASS.

Run: `php artisan route:list >/tmp/routes.txt`
Expected: command exits 0.

- [ ] **Step 4: Run frontend verification**

Run: `yarn run tsc && yarn run build`
Expected: both commands exit 0.

- [ ] **Step 5: Commit**

Run: `git add app tests && git commit -m "test: cover subdomain workflows"`

---

## Self-Review

- Spec coverage: admin Cloudflare domains, user tab, A/CNAME records, server limits, permissions, encrypted token storage, error handling, and testing all map to tasks.
- Placeholder scan: no deferred tasks or vague implementation-only steps remain; each task has exact files and commands.
- Type consistency: model names are `SubdomainDomain` and `Subdomain`; permission prefix is `subdomain`; server limit field is `subdomain_limit`; UI route is `/subdomains`.

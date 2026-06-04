# MC Versions Egg Generator Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build an admin Settings page that previews and syncs one managed `Minecraft Versions` nest with one generated egg per supported Minecraft server type.

**Architecture:** Keep the feature server-side in the existing Blade admin area. A catalog service defines supported server types and egg templates, while a generator service performs idempotent DB upserts for the managed nest, eggs, and variables. The controller only renders status and triggers preview/sync.

**Tech Stack:** Laravel 11, Eloquent models (`Nest`, `Egg`, `EggVariable`), Blade admin views, upstream Minecraft server jar APIs embedded in install scripts, PHPUnit for focused tests.

---

## File Structure

- Create `app/Services/Minecraft/McVersionsCatalogService.php`: supported server type definitions, startup commands, install scripts, variables, preview availability.
- Create `app/Services/Minecraft/McVersionsEggGeneratorService.php`: idempotent nest/egg/variable creation and preview actions.
- Create `app/Http/Controllers/Admin/Settings/McVersionsController.php`: renders page, handles preview and sync.
- Create `resources/views/admin/settings/mc-versions.blade.php`: Settings tab page with managed status and action forms.
- Modify `routes/admin.php`: add Settings routes for `mc-versions` and sync.
- Modify `resources/views/partials/admin/settings/nav.blade.php`: add `MC Versions` tab.
- Create `tests/CreatesApplication.php`, `tests/TestCase.php`, and feature tests if no local test harness exists.
- Create `tests/Feature/Admin/McVersionsEggGeneratorTest.php`: preview/sync/idempotency/unrelated-record tests.

---

### Task 1: Add Test Harness

**Files:**
- Create: `tests/CreatesApplication.php`
- Create: `tests/TestCase.php`
- Create: `phpunit.xml`

- [ ] **Step 1: Create the Laravel test bootstrap trait**

Create `tests/CreatesApplication.php`:

```php
<?php

namespace Pterodactyl\Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
```

- [ ] **Step 2: Create the base test case**

Create `tests/TestCase.php`:

```php
<?php

namespace Pterodactyl\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
    }
}
```

- [ ] **Step 3: Add PHPUnit config**

Create `phpunit.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
>
    <testsuites>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="CACHE_STORE" value="array"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="DB_CONNECTION" value="testing"/>
    </php>
</phpunit>
```

- [ ] **Step 4: Verify harness discovers no tests yet**

Run: `vendor/bin/phpunit --testsuite Feature`

Expected: exits successfully or reports no tests; it must not fail due to bootstrap/config errors.

- [ ] **Step 5: Commit harness**

```bash
git add tests/CreatesApplication.php tests/TestCase.php phpunit.xml
git commit -m "test: add phpunit harness"
```

---

### Task 2: Write Generator Feature Tests

**Files:**
- Create: `tests/Feature/Admin/McVersionsEggGeneratorTest.php`

- [ ] **Step 1: Add failing tests for preview and sync**

Create `tests/Feature/Admin/McVersionsEggGeneratorTest.php`:

```php
<?php

namespace Pterodactyl\Tests\Feature\Admin;

use Pterodactyl\Models\Egg;
use Pterodactyl\Models\Nest;
use Pterodactyl\Models\EggVariable;
use Pterodactyl\Tests\TestCase;
use Pterodactyl\Services\Minecraft\McVersionsEggGeneratorService;

class McVersionsEggGeneratorTest extends TestCase
{
    public function test_preview_reports_actions_without_writing_records(): void
    {
        $result = $this->app->make(McVersionsEggGeneratorService::class)->preview();

        $this->assertSame(0, Nest::query()->where('name', 'Minecraft Versions')->count());
        $this->assertNotEmpty($result['eggs']);
        $this->assertSame('create', $result['nest']['action']);
        $this->assertContains('Paper', array_column($result['eggs'], 'name'));
    }

    public function test_sync_creates_managed_nest_eggs_and_variables(): void
    {
        $result = $this->app->make(McVersionsEggGeneratorService::class)->sync();

        $nest = Nest::query()->where('name', 'Minecraft Versions')->firstOrFail();
        $paper = Egg::query()->where('nest_id', $nest->id)->where('name', 'Paper')->firstOrFail();

        $this->assertSame('mc-versions-generator@example.com', $nest->author);
        $this->assertStringContainsString('Managed by MC Versions generator.', $nest->description);
        $this->assertStringContainsString('SERVER_JARFILE', $paper->startup);
        $this->assertStringContainsString('api.papermc.io', $paper->script_install);
        $this->assertGreaterThanOrEqual(1, $result['created']);
        $this->assertDatabaseHas('egg_variables', [
            'egg_id' => $paper->id,
            'env_variable' => 'MINECRAFT_VERSION',
        ]);
        $this->assertDatabaseHas('egg_variables', [
            'egg_id' => $paper->id,
            'env_variable' => 'SERVER_JARFILE',
        ]);
    }

    public function test_sync_is_idempotent_and_keeps_egg_ids_stable(): void
    {
        $this->app->make(McVersionsEggGeneratorService::class)->sync();
        $firstNest = Nest::query()->where('name', 'Minecraft Versions')->firstOrFail();
        $firstPaper = Egg::query()->where('nest_id', $firstNest->id)->where('name', 'Paper')->firstOrFail();
        $firstVariableCount = EggVariable::query()->where('egg_id', $firstPaper->id)->count();

        $this->app->make(McVersionsEggGeneratorService::class)->sync();

        $this->assertSame(1, Nest::query()->where('name', 'Minecraft Versions')->count());
        $this->assertSame(1, Egg::query()->where('nest_id', $firstNest->id)->where('name', 'Paper')->count());
        $this->assertSame($firstPaper->id, Egg::query()->where('nest_id', $firstNest->id)->where('name', 'Paper')->value('id'));
        $this->assertSame($firstVariableCount, EggVariable::query()->where('egg_id', $firstPaper->id)->count());
    }

    public function test_sync_does_not_modify_unrelated_nests_or_eggs(): void
    {
        $nest = new Nest();
        $nest->forceFill([
            'uuid' => '00000000-0000-0000-0000-000000000001',
            'author' => 'admin@example.com',
            'name' => 'Minecraft',
            'description' => 'Existing nest',
        ])->save();

        $egg = new Egg();
        $egg->forceFill([
            'uuid' => '00000000-0000-0000-0000-000000000002',
            'nest_id' => $nest->id,
            'author' => 'admin@example.com',
            'name' => 'Paper',
            'description' => 'Existing egg',
            'docker_images' => ['Java 21' => 'ghcr.io/pterodactyl/yolks:java_21'],
            'startup' => 'java -jar server.jar',
            'config_stop' => 'stop',
            'config_startup' => '{}',
            'config_logs' => '{}',
            'config_files' => '{}',
            'script_is_privileged' => false,
            'script_install' => 'echo existing',
            'script_entry' => 'ash',
            'script_container' => 'ghcr.io/pterodactyl/installers:alpine',
            'force_outgoing_ip' => false,
        ])->save();

        $this->app->make(McVersionsEggGeneratorService::class)->sync();

        $this->assertSame('Existing nest', $nest->fresh()->description);
        $this->assertSame('echo existing', Egg::query()->where('nest_id', $nest->id)->where('name', 'Paper')->value('script_install'));
    }
}
```

- [ ] **Step 2: Run tests to verify feature classes are missing**

Run: `vendor/bin/phpunit tests/Feature/Admin/McVersionsEggGeneratorTest.php`

Expected: FAIL mentioning `Pterodactyl\Services\Minecraft\McVersionsEggGeneratorService` does not exist.

- [ ] **Step 3: Commit failing tests**

```bash
git add tests/Feature/Admin/McVersionsEggGeneratorTest.php
git commit -m "test: cover mc versions egg generator"
```

---

### Task 3: Add Catalog Service

**Files:**
- Create: `app/Services/Minecraft/McVersionsCatalogService.php`

- [ ] **Step 1: Create catalog service with concrete egg templates**

Create `app/Services/Minecraft/McVersionsCatalogService.php`:

```php
<?php

namespace Pterodactyl\Services\Minecraft;

class McVersionsCatalogService
{
    public const AUTHOR = 'mc-versions-generator@example.com';
    public const MARKER = 'Managed by MC Versions generator.';
    public const NEST_NAME = 'Minecraft Versions';

    public function definitions(): array
    {
        return [
            $this->paper(),
            $this->purpur(),
            $this->vanilla(),
            $this->fabric(),
            $this->forge(),
            $this->velocity(),
        ];
    }

    private function base(string $name, string $description, string $installScript, array $extraVariables = []): array
    {
        return [
            'name' => $name,
            'description' => self::MARKER . ' ' . $description,
            'features' => ['eula'],
            'docker_images' => [
                'Java 21' => 'ghcr.io/pterodactyl/yolks:java_21',
                'Java 17' => 'ghcr.io/pterodactyl/yolks:java_17',
            ],
            'startup' => 'java -Xms128M -XX:MaxRAMPercentage=95.0 -jar {{SERVER_JARFILE}}',
            'config_stop' => 'stop',
            'config_startup' => '{}',
            'config_logs' => '{}',
            'config_files' => '{}',
            'script_is_privileged' => false,
            'script_install' => $installScript,
            'script_entry' => 'ash',
            'script_container' => 'ghcr.io/pterodactyl/installers:alpine',
            'force_outgoing_ip' => false,
            'variables' => array_merge($this->commonVariables(), $extraVariables),
        ];
    }

    private function commonVariables(): array
    {
        return [
            [
                'name' => 'Minecraft Version',
                'description' => 'Minecraft version to install. Use latest for the newest supported release.',
                'env_variable' => 'MINECRAFT_VERSION',
                'default_value' => 'latest',
                'user_viewable' => true,
                'user_editable' => true,
                'rules' => 'required|string|max:32',
            ],
            [
                'name' => 'Server Jar File',
                'description' => 'The jar file name used by the startup command.',
                'env_variable' => 'SERVER_JARFILE',
                'default_value' => 'server.jar',
                'user_viewable' => true,
                'user_editable' => true,
                'rules' => 'required|regex:/^([\\w\\d._-]+)(\\.jar)$/',
            ],
        ];
    }

    private function paper(): array
    {
        return $this->base('Paper', 'Installs Paper from the PaperMC API.', <<<'SH'
#!/bin/ash
apk add --no-cache curl jq
cd /mnt/server
PROJECT=paper
USER_AGENT="Pterodactyl MC Versions Generator"
if [ -z "${MINECRAFT_VERSION}" ] || [ "${MINECRAFT_VERSION}" = "latest" ]; then
  MINECRAFT_VERSION=$(curl -sSL -A "${USER_AGENT}" https://api.papermc.io/v2/projects/${PROJECT} | jq -r '.versions[-1]')
fi
BUILD=$(curl -sSL -A "${USER_AGENT}" https://api.papermc.io/v2/projects/${PROJECT}/versions/${MINECRAFT_VERSION} | jq -r '.builds[-1]')
JAR=${PROJECT}-${MINECRAFT_VERSION}-${BUILD}.jar
curl -sSL -A "${USER_AGENT}" -o "${SERVER_JARFILE}" "https://api.papermc.io/v2/projects/${PROJECT}/versions/${MINECRAFT_VERSION}/builds/${BUILD}/downloads/${JAR}"
SH);
    }

    private function purpur(): array
    {
        return $this->base('Purpur', 'Installs Purpur from the Purpur API.', <<<'SH'
#!/bin/ash
apk add --no-cache curl jq
cd /mnt/server
if [ -z "${MINECRAFT_VERSION}" ] || [ "${MINECRAFT_VERSION}" = "latest" ]; then
  MINECRAFT_VERSION=$(curl -sSL https://api.purpurmc.org/v2/purpur | jq -r '.versions[-1]')
fi
BUILD=$(curl -sSL https://api.purpurmc.org/v2/purpur/${MINECRAFT_VERSION} | jq -r '.builds.latest')
curl -sSL -o "${SERVER_JARFILE}" "https://api.purpurmc.org/v2/purpur/${MINECRAFT_VERSION}/${BUILD}/download"
SH);
    }

    private function vanilla(): array
    {
        return $this->base('Vanilla', 'Installs Vanilla from Mojang manifests.', <<<'SH'
#!/bin/ash
apk add --no-cache curl jq
cd /mnt/server
MANIFEST=$(curl -sSL https://launchermeta.mojang.com/mc/game/version_manifest.json)
if [ -z "${MINECRAFT_VERSION}" ] || [ "${MINECRAFT_VERSION}" = "latest" ]; then
  MINECRAFT_VERSION=$(echo "${MANIFEST}" | jq -r '.latest.release')
fi
VERSION_URL=$(echo "${MANIFEST}" | jq -r --arg VERSION "${MINECRAFT_VERSION}" '.versions[] | select(.id == $VERSION) | .url')
DOWNLOAD_URL=$(curl -sSL "${VERSION_URL}" | jq -r '.downloads.server.url')
curl -sSL -o "${SERVER_JARFILE}" "${DOWNLOAD_URL}"
SH);
    }

    private function fabric(): array
    {
        return $this->base('Fabric', 'Installs Fabric loader from Fabric metadata.', <<<'SH'
#!/bin/ash
apk add --no-cache curl jq
cd /mnt/server
if [ -z "${MINECRAFT_VERSION}" ] || [ "${MINECRAFT_VERSION}" = "latest" ]; then
  MINECRAFT_VERSION=$(curl -sSL https://meta.fabricmc.net/v2/versions/game | jq -r '[.[] | select(.stable == true)][0].version')
fi
LOADER_VERSION=$(curl -sSL https://meta.fabricmc.net/v2/versions/loader | jq -r '.[0].version')
INSTALLER_VERSION=$(curl -sSL https://meta.fabricmc.net/v2/versions/installer | jq -r '.[0].version')
curl -sSL -o "${SERVER_JARFILE}" "https://meta.fabricmc.net/v2/versions/loader/${MINECRAFT_VERSION}/${LOADER_VERSION}/${INSTALLER_VERSION}/server/jar"
SH);
    }

    private function forge(): array
    {
        return $this->base('Forge', 'Installs Forge using Forge promotions metadata.', <<<'SH'
#!/bin/ash
apk add --no-cache curl jq bash
cd /mnt/server
if [ -z "${MINECRAFT_VERSION}" ] || [ "${MINECRAFT_VERSION}" = "latest" ]; then
  MINECRAFT_VERSION=$(curl -sSL https://files.minecraftforge.net/net/minecraftforge/forge/promotions_slim.json | jq -r '.promos | keys[] | select(endswith("-latest")) | split("-")[0]' | sort -V | tail -1)
fi
FORGE_VERSION=$(curl -sSL https://files.minecraftforge.net/net/minecraftforge/forge/promotions_slim.json | jq -r --arg MC "${MINECRAFT_VERSION}-latest" '.promos[$MC]')
DOWNLOAD="https://maven.minecraftforge.net/net/minecraftforge/forge/${MINECRAFT_VERSION}-${FORGE_VERSION}/forge-${MINECRAFT_VERSION}-${FORGE_VERSION}-installer.jar"
curl -sSL -o installer.jar "${DOWNLOAD}"
java -jar installer.jar --installServer
if [ -f forge-${MINECRAFT_VERSION}-${FORGE_VERSION}.jar ]; then
  mv forge-${MINECRAFT_VERSION}-${FORGE_VERSION}.jar "${SERVER_JARFILE}"
fi
rm -f installer.jar
SH);
    }

    private function velocity(): array
    {
        $definition = $this->base('Velocity', 'Installs Velocity from the PaperMC API.', <<<'SH'
#!/bin/ash
apk add --no-cache curl jq
cd /mnt/server
PROJECT=velocity
USER_AGENT="Pterodactyl MC Versions Generator"
if [ -z "${MINECRAFT_VERSION}" ] || [ "${MINECRAFT_VERSION}" = "latest" ]; then
  MINECRAFT_VERSION=$(curl -sSL -A "${USER_AGENT}" https://api.papermc.io/v2/projects/${PROJECT} | jq -r '.versions[-1]')
fi
BUILD=$(curl -sSL -A "${USER_AGENT}" https://api.papermc.io/v2/projects/${PROJECT}/versions/${MINECRAFT_VERSION} | jq -r '.builds[-1]')
JAR=${PROJECT}-${MINECRAFT_VERSION}-${BUILD}.jar
curl -sSL -A "${USER_AGENT}" -o "${SERVER_JARFILE}" "https://api.papermc.io/v2/projects/${PROJECT}/versions/${MINECRAFT_VERSION}/builds/${BUILD}/downloads/${JAR}"
SH);
        $definition['startup'] = 'java -Xms128M -XX:MaxRAMPercentage=95.0 -jar {{SERVER_JARFILE}}';

        return $definition;
    }
}
```

- [ ] **Step 2: Run tests to verify generator service still missing**

Run: `vendor/bin/phpunit tests/Feature/Admin/McVersionsEggGeneratorTest.php`

Expected: FAIL mentioning `McVersionsEggGeneratorService` does not exist.

- [ ] **Step 3: Commit catalog service**

```bash
git add app/Services/Minecraft/McVersionsCatalogService.php
git commit -m "feat: define mc versions egg catalog"
```

---

### Task 4: Add Generator Service

**Files:**
- Create: `app/Services/Minecraft/McVersionsEggGeneratorService.php`

- [ ] **Step 1: Implement idempotent preview and sync**

Create `app/Services/Minecraft/McVersionsEggGeneratorService.php`:

```php
<?php

namespace Pterodactyl\Services\Minecraft;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Pterodactyl\Models\Egg;
use Pterodactyl\Models\Nest;
use Pterodactyl\Models\EggVariable;

class McVersionsEggGeneratorService
{
    public function __construct(private readonly McVersionsCatalogService $catalog)
    {
    }

    public function preview(): array
    {
        $nest = $this->managedNest();

        return [
            'nest' => [
                'name' => McVersionsCatalogService::NEST_NAME,
                'action' => $nest ? 'update' : 'create',
            ],
            'eggs' => array_map(function (array $definition) use ($nest) {
                $egg = $nest ? $this->managedEgg($nest, $definition['name']) : null;

                return [
                    'name' => $definition['name'],
                    'action' => $egg ? 'update' : 'create',
                    'available' => true,
                ];
            }, $this->catalog->definitions()),
        ];
    }

    public function sync(): array
    {
        return DB::transaction(function () {
            $created = 0;
            $updated = 0;
            $skipped = 0;
            $failed = [];

            $nest = $this->managedNest();
            if (!$nest) {
                $nest = new Nest();
                $nest->forceFill([
                    'uuid' => (string) Str::uuid(),
                    'author' => McVersionsCatalogService::AUTHOR,
                    'name' => McVersionsCatalogService::NEST_NAME,
                    'description' => McVersionsCatalogService::MARKER,
                ])->save();
                ++$created;
            } else {
                $nest->forceFill([
                    'description' => McVersionsCatalogService::MARKER,
                ])->save();
                ++$updated;
            }

            foreach ($this->catalog->definitions() as $definition) {
                try {
                    $egg = $this->upsertEgg($nest, $definition);
                    $egg['created'] ? ++$created : ++$updated;
                } catch (\Throwable $exception) {
                    ++$skipped;
                    $failed[] = [
                        'name' => $definition['name'] ?? 'Unknown',
                        'reason' => $exception->getMessage(),
                    ];
                }
            }

            return compact('created', 'updated', 'skipped', 'failed');
        });
    }

    private function managedNest(): ?Nest
    {
        return Nest::query()
            ->where('name', McVersionsCatalogService::NEST_NAME)
            ->where('author', McVersionsCatalogService::AUTHOR)
            ->first();
    }

    private function managedEgg(Nest $nest, string $name): ?Egg
    {
        return Egg::query()
            ->where('nest_id', $nest->id)
            ->where('name', $name)
            ->where('author', McVersionsCatalogService::AUTHOR)
            ->where('description', 'like', McVersionsCatalogService::MARKER . '%')
            ->first();
    }

    private function upsertEgg(Nest $nest, array $definition): array
    {
        $egg = $this->managedEgg($nest, $definition['name']);
        $created = false;
        $payload = Arr::except($definition, ['variables']);
        $payload['nest_id'] = $nest->id;
        $payload['author'] = McVersionsCatalogService::AUTHOR;

        if (!$egg) {
            $created = true;
            $egg = new Egg();
            $egg->forceFill(array_merge($payload, [
                'uuid' => (string) Str::uuid(),
            ]))->save();
        } else {
            $egg->forceFill($payload)->save();
        }

        foreach ($definition['variables'] as $variable) {
            $this->upsertVariable($egg, $variable);
        }

        return ['egg' => $egg, 'created' => $created];
    }

    private function upsertVariable(Egg $egg, array $definition): EggVariable
    {
        $variable = EggVariable::query()
            ->where('egg_id', $egg->id)
            ->where('env_variable', $definition['env_variable'])
            ->first();

        $payload = array_merge($definition, ['egg_id' => $egg->id]);

        if (!$variable) {
            return EggVariable::query()->create($payload);
        }

        $variable->forceFill($payload)->save();

        return $variable;
    }
}
```

- [ ] **Step 2: Run focused tests**

Run: `vendor/bin/phpunit tests/Feature/Admin/McVersionsEggGeneratorTest.php`

Expected: PASS.

- [ ] **Step 3: Commit generator service**

```bash
git add app/Services/Minecraft/McVersionsEggGeneratorService.php
git commit -m "feat: sync mc versions eggs"
```

---

### Task 5: Add Admin Controller, Routes, Nav, And View

**Files:**
- Create: `app/Http/Controllers/Admin/Settings/McVersionsController.php`
- Create: `resources/views/admin/settings/mc-versions.blade.php`
- Modify: `routes/admin.php`
- Modify: `resources/views/partials/admin/settings/nav.blade.php`

- [ ] **Step 1: Create controller**

Create `app/Http/Controllers/Admin/Settings/McVersionsController.php`:

```php
<?php

namespace Pterodactyl\Http\Controllers\Admin\Settings;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Minecraft\McVersionsEggGeneratorService;

class McVersionsController extends Controller
{
    public function __construct(
        private readonly AlertsMessageBag $alert,
        private readonly McVersionsEggGeneratorService $generator,
    ) {
    }

    public function index(): View
    {
        return view('admin.settings.mc-versions', [
            'preview' => $this->generator->preview(),
            'syncResult' => session('mc_versions_sync'),
        ]);
    }

    public function sync(): RedirectResponse
    {
        $result = $this->generator->sync();

        $this->alert->success(sprintf(
            'MC Versions sync complete: %d created, %d updated, %d skipped.',
            $result['created'],
            $result['updated'],
            $result['skipped'],
        ))->flash();

        foreach ($result['failed'] as $failure) {
            $this->alert->warning(sprintf('%s skipped: %s', $failure['name'], $failure['reason']))->flash();
        }

        return redirect()->route('admin.settings.mc-versions')->with('mc_versions_sync', $result);
    }
}
```

- [ ] **Step 2: Add settings routes**

Modify `routes/admin.php` inside the existing `Route::group(['prefix' => 'settings'], function () { ... });` block:

```php
    Route::get('/mc-versions', [Admin\Settings\McVersionsController::class, 'index'])->name('admin.settings.mc-versions');
    Route::post('/mc-versions/sync', [Admin\Settings\McVersionsController::class, 'sync'])->name('admin.settings.mc-versions.sync');
```

Place these after the existing `advanced` GET route and before the `mail/test` POST route.

- [ ] **Step 3: Add settings nav tab**

Modify `resources/views/partials/admin/settings/nav.blade.php` and add this tab after `Advanced`:

```blade
                    <li @if($activeTab === 'mc-versions')class="active"@endif><a href="{{ route('admin.settings.mc-versions') }}">MC Versions</a></li>
```

- [ ] **Step 4: Create Blade page**

Create `resources/views/admin/settings/mc-versions.blade.php`:

```blade
@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'mc-versions'])

@section('title')
    MC Versions
@endsection

@section('content-header')
    <h1>MC Versions<small>Generate managed Minecraft server eggs.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.settings') }}">Settings</a></li>
        <li class="active">MC Versions</li>
    </ol>
@endsection

@section('content')
    @yield('settings::nav')
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Managed Nest Preview</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <tbody>
                            <tr>
                                <th>Resource</th>
                                <th>Name</th>
                                <th>Action</th>
                                <th>Status</th>
                            </tr>
                            <tr>
                                <td>Nest</td>
                                <td>{{ $preview['nest']['name'] }}</td>
                                <td><code>{{ $preview['nest']['action'] }}</code></td>
                                <td>Ready</td>
                            </tr>
                            @foreach($preview['eggs'] as $egg)
                                <tr>
                                    <td>Egg</td>
                                    <td>{{ $egg['name'] }}</td>
                                    <td><code>{{ $egg['action'] }}</code></td>
                                    <td>{{ $egg['available'] ? 'Ready' : 'Skipped' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="box-footer">
                    <form action="{{ route('admin.settings.mc-versions.sync') }}" method="POST">
                        {!! csrf_field() !!}
                        <a href="{{ route('admin.settings.mc-versions') }}" class="btn btn-sm btn-default">Refresh Preview</a>
                        <button type="submit" class="btn btn-sm btn-primary pull-right">Sync Eggs</button>
                    </form>
                    <p class="text-muted no-margin">Sync creates or updates only eggs marked as managed by the MC Versions generator.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
```

- [ ] **Step 5: Route sanity check**

Run: `php artisan route:list --name=admin.settings.mc-versions`

Expected: output includes:

```text
admin.settings.mc-versions
admin.settings.mc-versions.sync
```

- [ ] **Step 6: Run focused tests**

Run: `vendor/bin/phpunit tests/Feature/Admin/McVersionsEggGeneratorTest.php`

Expected: PASS.

- [ ] **Step 7: Commit admin UI**

```bash
git add app/Http/Controllers/Admin/Settings/McVersionsController.php resources/views/admin/settings/mc-versions.blade.php routes/admin.php resources/views/partials/admin/settings/nav.blade.php
git commit -m "feat: add mc versions admin page"
```

---

### Task 6: Final Verification

**Files:**
- Modify only if verification exposes issues in files from Tasks 1-5.

- [ ] **Step 1: Run PHP syntax checks on new PHP files**

Run:

```bash
php -l app/Services/Minecraft/McVersionsCatalogService.php
php -l app/Services/Minecraft/McVersionsEggGeneratorService.php
php -l app/Http/Controllers/Admin/Settings/McVersionsController.php
```

Expected: each command prints `No syntax errors detected`.

- [ ] **Step 2: Run focused test suite**

Run: `vendor/bin/phpunit tests/Feature/Admin/McVersionsEggGeneratorTest.php`

Expected: PASS.

- [ ] **Step 3: Run route check**

Run: `php artisan route:list --name=admin.settings.mc-versions`

Expected: both MC Versions routes listed.

- [ ] **Step 4: Inspect dirty worktree**

Run: `git status --short`

Expected: only intentional files from this plan are changed. Pre-existing dirty files such as `config/arix.php` and `Screenshot 2026-06-04 175018.png` may still appear and must not be reverted.

- [ ] **Step 5: Commit verification fixes if any were needed**

If verification required fixes, commit only touched feature/test files:

```bash
git add app/Services/Minecraft app/Http/Controllers/Admin/Settings/McVersionsController.php resources/views/admin/settings/mc-versions.blade.php routes/admin.php resources/views/partials/admin/settings/nav.blade.php tests phpunit.xml
git commit -m "fix: verify mc versions egg generator"
```

If no fixes were needed, do not create an empty commit.

# Admin Logs Tab Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add /admin/logs tab that shows only admin actions + failed login attempts by reusing the existing ActivityLog system.

**Architecture:** Extend ActivityLog + ActivityLogService (already used on client routes) to admin routes via new middleware; extend AuthenticationListener for failed logins; new Blade controller + view under layouts.admin with GET filters + pagination.

**Tech Stack:** Laravel 10, Blade, existing ActivityLog model/service, no new packages.

---

### Task 1: Backup Database

**Files:** none (ops step)

- [ ] **Step 1: Run backup command**

```bash
mysqldump -u root -p pterodactyl > /tmp/pterodactyl-backup-$(date +%Y%m%d-%H%M%S).sql
```

Expected: SQL dump created, no errors.

- [ ] **Step 2: Verify backup file exists and size > 0**

```bash
ls -lh /tmp/pterodactyl-backup-*.sql | tail -1
```

---

### Task 2: Create Admin Activity Middleware

**Files:**
- Create: `app/Http/Middleware/Activity/AdminSubject.php`

- [ ] **Step 1: Write the middleware file**

```php
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
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Middleware/Activity/AdminSubject.php
git commit -m "feat: add AdminSubject middleware for activity logging on admin routes"
```

---

### Task 3: Register Middleware and Extend AuthenticationListener

**Files:**
- Modify: `app/Providers/EventServiceProvider.php:40-50`
- Modify: `app/Listeners/Auth/AuthenticationListener.php:20-35`

- [ ] **Step 1: Add middleware alias in Kernel (if not already present)**

Check `app/Http/Kernel.php` for `$routeMiddleware` — if missing, add:

```php
'activity' => \Pterodactyl\Http\Middleware\Activity\AccountSubject::class,
'activity.admin' => \Pterodactyl\Http\Middleware\Activity\AdminSubject::class,
```

- [ ] **Step 2: Extend AuthenticationListener to ensure failed login is logged with admin context**

Read current `AuthenticationListener.php` then append inside `login(Failed $event)` method:

```php
// Already logs auth:fail — ensure it runs for admin panel logins too (no change needed if event fires globally)
```

- [ ] **Step 3: Commit**

```bash
git add app/Providers/EventServiceProvider.php app/Http/Kernel.php app/Listeners/Auth/AuthenticationListener.php
git commit -m "feat: register admin activity middleware and confirm failed login listener"
```

---

### Task 4: Apply Middleware to Admin Routes

**Files:**
- Modify: `routes/admin.php` (or routes where admin group is defined)

- [ ] **Step 1: Wrap admin routes with activity.admin middleware**

Find the admin route group and add middleware:

```php
Route::middleware(['auth', 'activity.admin'])->group(function () {
    // existing admin routes
});
```

- [ ] **Step 2: Commit**

```bash
git add routes/admin.php
git commit -m "feat: apply activity.admin middleware to admin routes"
```

---

### Task 5: Create LogsController and Route

**Files:**
- Create: `app/Http/Controllers/Admin/LogsController.php`
- Modify: `routes/admin.php` (add GET /logs)

- [ ] **Step 1: Write the controller**

```php
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
```

- [ ] **Step 2: Add route**

In `routes/admin.php`:

```php
Route::get('/logs', [LogsController::class, 'index'])->name('admin.logs');
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/Admin/LogsController.php routes/admin.php
git commit -m "feat: add LogsController and /admin/logs route"
```

---

### Task 6: Create Blade View

**Files:**
- Create: `resources/views/admin/logs/index.blade.php`

- [ ] **Step 1: Write the Blade view (match users/index.blade.php pattern)**

```blade
@extends('layouts.admin')

@section('title', 'Activity Logs')

@section('content-header')
    <h1>Admin Activity Logs</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Logs</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Admin</th>
                                <th>Event</th>
                                <th>Target</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->timestamp }}</td>
                                    <td>{{ $log->actor?->username ?? 'System' }}</td>
                                    <td><code>{{ $log->event }}</code></td>
                                    <td>{{ $log->subject_type ? class_basename($log->subject_type).'#'.$log->subject_id : '-' }}</td>
                                    <td>{{ $log->ip }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($logs->hasPages())
                    <div class="box-footer with-border">
                        {{ $logs->appends(request()->query())->render() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/admin/logs/index.blade.php
git commit -m "feat: add admin logs index blade view"
```

---

### Task 7: Verify End-to-End

**Files:** none

- [ ] **Step 1: Clear route + config cache**

```bash
php artisan route:clear && php artisan config:clear
```

- [ ] **Step 2: Login as admin, change a setting, create a user, then attempt bad login from another browser**

- [ ] **Step 3: Visit /admin/logs and confirm entries appear with correct actor, event, IP**

Expected: 3+ rows (settings change, user create, auth:fail) showing admin username.

- [ ] **Step 4: Confirm client routes still log normally and are NOT shown in /admin/logs**

---

Plan complete and saved to `docs/superpowers/plans/2026-06-07-admin-logs-tab.md`. Two execution options:

1. Subagent-Driven (recommended) — I dispatch a fresh subagent per task, review between tasks, fast iteration
2. Inline Execution — Execute tasks in this session using executing-plans, batch execution with checkpoints

Which approach?
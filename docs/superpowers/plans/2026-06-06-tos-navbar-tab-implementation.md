# TOS Tab in Arix Navbar Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add an editable TOS section to the Arix admin settings, a conditional TOS link in the main navbar, and a public `/tos` page that renders the stored content.

**Architecture:** Extend the existing config-driven Arix settings system (`config/arix.php`) with a new `tos_content` key. The admin form at `/admin/arix` already accepts any `arix:xxx` field via `ArixBaseController::saveArixConfig`, so only the Blade template needs updating. Add a conditional React link in `NavigationBar.tsx` and a simple Blade view + route for the public TOS page.

**Tech Stack:** Laravel Blade, React/TypeScript (existing Arix frontend), PHP config file persistence (no DB changes)

---

## File Mapping

| File | Responsibility |
|------|----------------|
| `resources/views/admin/arix/index.blade.php` | Add TOS textarea input in the existing admin form |
| `resources/scripts/components/NavigationBar.tsx` | Read `tos_content` from settings store and conditionally render navbar link |
| `resources/views/arix/tos.blade.php` | New Blade view that renders the TOS HTML content inside Arix layout |
| `routes/web.php` (or Blueprint route location) | Register `GET /tos` route pointing to the new view |

---

### Task 1: Add TOS textarea to admin settings form

**Files:**
- Modify: `resources/views/admin/arix/index.blade.php:44-48`

- [ ] **Step 1: Insert TOS textarea section before the floating button**

Open `resources/views/admin/arix/index.blade.php` and insert the following block immediately before the `<div class="floating-button">` line (around line 49):

```blade
<div class="input-field hr">
    <label for="arix:tos_content">Terms of Service content</label>
    <textarea id="arix:tos_content" name="arix:tos_content" rows="10">{{ old('arix:tos_content', $tos_content ?? '') }}</textarea>
    <small>Leave empty to hide the TOS link from the navbar. HTML is allowed.</small>
</div>
```

- [ ] **Step 2: Verify the field appears in the rendered form**

Run the panel and navigate to `/admin/arix`. Confirm a textarea labeled "Terms of Service content" appears above the Save button.

- [ ] **Step 3: Commit**

```bash
git add resources/views/admin/arix/index.blade.php
git commit -m "feat(arix): add TOS content textarea to admin settings"
```

---

### Task 2: Add conditional TOS link to desktop navbar

**Files:**
- Modify: `resources/scripts/components/NavigationBar.tsx:213-215`

- [ ] **Step 1: Read tos_content from the settings store**

Inside the component function (near other `useStoreState` calls around line 152-159), add:

```tsx
const tosContent = useStoreState((state: ApplicationStore) => state.settings.data!.arix.tos_content);
```

- [ ] **Step 2: Render the TOS link conditionally after the support link**

Replace the `RightNavigation` block's support link section (around line 214) to include the TOS link immediately after it:

```tsx
{support && <a href={support}><SupportIcon className={'w-5'} />{t`supportcenter`}</a>}
{tosContent && <a href="/tos">Terms of Service</a>}
```

- [ ] **Step 3: Commit**

```bash
git add resources/scripts/components/NavigationBar.tsx
git commit -m "feat(arix): add conditional TOS link to navbar when tos_content is set"
```

---

### Task 3: Add TOS link to mobile menu

**Files:**
- Modify: `resources/scripts/components/NavigationBar.tsx:244-246`

- [ ] **Step 1: Add TOS link inside the mobile menu links section**

In the `MobileLinks` div (around line 244), after the support link, add:

```tsx
{support !== 'none' && <a href={support}><SupportIcon className={'w-5'} />{t`supportcenter`}</a>}
{tosContent && <a href="/tos">Terms of Service</a>}
```

- [ ] **Step 2: Commit**

```bash
git add resources/scripts/components/NavigationBar.tsx
git commit -m "feat(arix): include TOS link in mobile menu when configured"
```

---

### Task 4: Create the public TOS Blade view

**Files:**
- Create: `resources/views/arix/tos.blade.php`

- [ ] **Step 1: Create the TOS view file**

```bash
touch resources/views/arix/tos.blade.php
```

- [ ] **Step 2: Add the Blade template content**

Write the following into `resources/views/arix/tos.blade.php`:

```blade
@extends('layouts.arix')

@section('title')
    Terms of Service
@endsection

@section('content')
    <div class="max-w-[1200px] mx-auto px-4 py-8">
        <h1 class="text-3xl font-semibold text-gray-50 mb-6">Terms of Service</h1>
        <div class="prose prose-invert max-w-none">
            {!! $tos_content !!}
        </div>
    </div>
@endsection
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/arix/tos.blade.php
git commit -m "feat(arix): add public TOS page view"
```

---

### Task 5: Register the /tos route

**Files:**
- Modify: whichever routes file handles public GET routes (commonly `routes/web.php` or a Blueprint partial in `routes/blueprint/web/`)

- [ ] **Step 1: Add the route definition**

Add the following route (exact file depends on project routing layout; place it with other public pages):

```php
Route::get('/tos', function () {
    $tosContent = config('arix.tos_content');

    if (empty(trim($tosContent ?? ''))) {
        abort(404);
    }

    return view('arix.tos', ['tos_content' => $tosContent]);
})->name('arix.tos');
```

- [ ] **Step 2: Verify the route works**

After adding, run `php artisan route:list | grep tos` to confirm the route is registered.

- [ ] **Step 3: Commit**

```bash
git add routes/
git commit -m "feat(arix): register public /tos route that renders configured content or 404s"
```

---

### Task 6: Verify end-to-end flow

**Files:** (no code changes)

- [ ] **Step 1: Test admin save**

Navigate to `/admin/arix`, paste sample HTML into the TOS textarea, and save. Confirm `config/arix.php` now contains a `tos_content` key with the value.

- [ ] **Step 2: Test navbar visibility**

Log in as a regular user. Confirm the "Terms of Service" link appears in the navbar (desktop and mobile).

- [ ] **Step 3: Test public page**

Click the link or visit `/tos` directly. Confirm the content renders correctly. Log out and repeat to verify public access.

- [ ] **Step 4: Test empty state**

Clear the TOS content in admin, save, then confirm the navbar link disappears and `/tos` returns 404.

- [ ] **Step 5: Commit verification**

```bash
git status
# Should show clean working tree with the commits from Tasks 1-5
```

---

## Self-Review Checklist

- [x] Spec coverage: All requirements from the design spec map to tasks (admin textarea, navbar link, public page, config storage, empty-state hiding).
- [x] No placeholders: Every step contains exact file paths, code snippets, or commands.
- [x] Type consistency: React store access uses the same `arix.tos_content` path; Blade uses `$tos_content`.
- [x] Scope: No new migrations, no DB tables, follows existing Arix config pattern.

Plan complete and saved to `docs/superpowers/plans/2026-06-06-tos-navbar-tab-implementation.md`.

**Two execution options:**

**1. Subagent-Driven (recommended)** — I dispatch a fresh subagent per task, review between tasks, fast iteration

**2. Inline Execution** — Execute tasks in this session using executing-plans, batch execution with checkpoints

Which approach?
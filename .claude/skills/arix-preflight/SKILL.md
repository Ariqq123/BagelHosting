---
name: arix-preflight
description: Pre-flight checklist and guardrail for any Arix or Blueprint development work. MUST be invoked before starting work on TOS pages, navbar changes, admin settings forms, theme customizations, config/arix.php modifications, or any task involving the Arix theme or Blueprint extensions. Prevents common mistakes like wrong branch names (fixs vs fixes), staging unrelated files (freeservers symlink), forgetting to re-enable commented routes, using the wrong layout (layouts.arix for user pages), missing cache clears after config changes, permission issues on config/arix.php, and incomplete Blueprint extensions that break the panel.
---

# Arix Pre-Flight Checklist

**This skill is MANDATORY for any Arix/Blueprint work.** Do not skip it. The mistakes it prevents have already caused multiple 500 errors, 404s, and broken commits in this codebase.

## When to Use

Use this skill when the user mentions any of the following:
- TOS / Terms of Service page
- Navbar modifications
- Admin settings (`/admin/arix`)
- Arix theme customization
- `config/arix.php` changes
- Blueprint extension routes
- Any task involving the `arix/` directory or Arix React components

## Pre-Flight Checklist (MANDATORY)

You MUST complete every step below before writing any code or making any changes. Use tools to verify — do not trust memory.

### 1. Branch Name Verification

**Action:** Check the current branch name.

```bash
git branch --show-current
```

**Verify:**
- The branch name is spelled correctly (common typo: `arix-blueprint-theme-fixs` instead of `arix-blueprint-theme-fixes`)
- The branch follows the project's naming convention
- If the branch name looks wrong, stop and ask the user before proceeding

**Why:** A single typo in the branch name caused the entire TOS feature to be committed to the wrong branch and required multiple `git` operations to fix.

### 2. Unrelated Files Check

**Action:** Check git status for untracked or unstaged files that are unrelated to the current task.

```bash
git status --short
```

**Verify:**
- No unexpected symlinks in `routes/blueprint/client/` (e.g., `freeservers.php`)
- No untracked Blueprint extension directories under `.blueprint/extensions/` or `app/BlueprintFramework/Extensions/`
- No partial extension files (migrations, controllers, routes) that would break panel if not fully installed via blueprint CLI
- If unrelated files appear, explicitly ask the user whether they should be included before staging anything

**Why:** Incomplete or untracked Blueprint extensions (freeservers, team-launcher, etc.) cause panel 500s, missing routes, and broken admin pages when the extension is half-present but not registered in `installed_extensions`.

### 3. Blueprint Extension Integrity Check

**Action:** Scan for Blueprint extension artifacts that could break the panel.

```bash
git status --short | grep -E "(\.blueprint/extensions|BlueprintFramework/Extensions|installed_extensions)"
ls -la .blueprint/extensions/ 2>/dev/null || true
```

**Verify:**
- No dangling extension folders without corresponding entry in `.blueprint/extensions/blueprint/private/db/installed_extensions`
- No extension route/controller files present unless the extension is fully installed via `blueprint -install`
- Symlinks in `routes/blueprint/client/` only exist for properly installed extensions

**Why:** Partial Blueprint extension files (migrations, controllers, routes) without proper registration cause panel breakage, missing admin pages, and 500 errors on load.

### 4. Commented-Out Routes Check

**Action:** Search for commented-out routes in `routes/base.php` (or any route file you're modifying).

```bash
grep -n "// Route::" routes/base.php | head -20
```

**Verify:**
- No `/tos` route (or similar) is commented out from a previous debugging session
- If a route is commented out, uncomment it and verify it works before considering the task complete

**Why:** The `/tos` route was left commented out after a 500 debugging session, causing persistent 404s even after content was saved.

### 5. Layout Choice Verification (If Creating/Editing Blade Views)

**Action:** If the task involves creating or editing a `.blade.php` file, determine the correct layout.

**Rules:**
- `layouts.arix` → ONLY for admin editor pages (`/admin/arix/*`)
- `templates/base/core` or `templates/wrapper` → For user-facing pages (navbar, public content, `/tos`, etc.)

**Verify:**
- The view extends the correct layout for its intended audience
- If using `layouts.arix` for a user-facing page, change it immediately

**Why:** Using `layouts.arix` for the public TOS page caused `Auth::user()` to be null for logged-out users, resulting in a 500 error.

### 6. Permission Check on `config/arix.php`

**Action:** Verify the web server can write to the Arix config file.

```bash
ls -la config/arix.php
```

**Verify:**
- Owner is `www-data:www-data` (or equivalent web server user)
- Permissions are at least `664`
- If not, run:
  ```bash
  sudo chown www-data:www-data config/arix.php
  sudo chmod 664 config/arix.php
  ```

**Why:** Multiple saves to `/admin/arix` failed with `Permission denied` because `config/arix.php` was not writable by the web server.

### 7. Cache Clearing Strategy

**Action:** Plan when caches must be cleared.

**Rules:**
- After any change to `config/arix.php` → `php artisan config:clear`
- After any route change in `routes/base.php` or `routes/blueprint/` → `php artisan route:clear`
- After any view change → `php artisan view:clear`

**Verify:**
- You have included the appropriate cache clear command(s) in your implementation plan or commit message
- If the user reports a 404 or 500 after a config/route change, the first thing to try is clearing the relevant cache

**Why:** Stale route/config caches caused the freeservers 404 and the TOS page not appearing even after content was saved.

---

## Execution

After completing the checklist above:

1. Create a `TodoWrite` with each checklist item as a task
2. Mark items as completed only after verification (not before)
3. If any item fails verification, stop and resolve it before proceeding with the actual work

**Do not skip this skill.** The mistakes it prevents are real, have already happened in this codebase, and will happen again without this guardrail.
# Free Servers Arix Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restyle the Blueprint FreeServers user-facing page and dashboard banner so they match the installed Arix theme while preserving the existing FreeServers behavior and API contract.

**Architecture:** Keep the redesign inside the existing Blueprint extension React components. Use Arix utility classes and CSS variables already present in the panel instead of adding a new design system or changing backend routes/controllers.

**Tech Stack:** Blueprint extension components, React 16, TypeScript, Tailwind utilities, styled-components-compatible CSS vars, existing FontAwesome icons, existing Arix theme tokens.

---

## File Map

- Modify `resources/scripts/blueprint/extensions/freeservers/FreeServersPageContent.tsx`: redesign the main account provisioning workflow.
- Modify `resources/scripts/blueprint/extensions/freeservers/FreeServersBanner.tsx`: redesign the dashboard banner.
- Optional modify `resources/scripts/blueprint/extensions/freeservers/FreeServersBannerWrapper.tsx`: only if the banner needs a richer prop shape already returned by the API.
- Optional modify `resources/scripts/blueprint/extensions/freeservers/components/i18n/translations.ts`: only if an existing key is missing for required existing copy. Do not add decorative copy.

Do not modify:

- `.blueprint/extensions/freeservers/app/FreeServersController.php`
- `app/Http/Controllers/Admin/Extensions/freeservers/freeserversExtensionController.php`
- `resources/views/admin/extensions/freeservers/index.blade.php`
- database migrations
- Arix global config

---

### Task 1: Add Arix-Native Styling Primitives In The FreeServers Page

**Files:**
- Modify: `resources/scripts/blueprint/extensions/freeservers/FreeServersPageContent.tsx`

- [ ] **Step 1: Introduce local class constants**

Add local constants near `formatMemory` for repeated Arix surfaces and controls:

```ts
const panelClass = 'bg-gray-700 backdrop border border-gray-500 rounded-box';
const innerPanelClass = 'bg-gray-600/60 border border-gray-500 rounded-component';
const mutedTextClass = 'text-gray-300';
const selectedCardClass = 'border-arix bg-gray-600';
const idleCardClass = 'border-gray-500 bg-gray-600 hover:border-gray-400';
```

Adjust exact opacity utilities only if Tailwind build rejects the slash opacity form.

- [ ] **Step 2: Replace green header with Arix provisioning header**

Replace the `bg-green-700` header with a `panelClass` section that contains:

- title `t('pageTitle')`
- subtitle `createUpTo` with `max_servers`
- stat chip for `current_servers / max_servers`
- stat chip for `remaining`

Use `text-arix` and `border-arix` for accent. Avoid hardcoded green.

- [ ] **Step 3: Restyle state messages**

Restyle unavailable and limit reached panels using Arix dark surfaces:

- unavailable icon can remain warning/yellow
- panel surface uses `panelClass`
- headings use `text-gray-50`
- body uses `text-gray-300`

Restyle success and error messages using Arix semantic colors where possible:

- error: `text-danger-50` with `border-danger-100` and `color-mix(... var(--dangerBackground) ...)`
- success: `text-success-50` with `border-success-100` and `color-mix(... var(--successBackground) ...)`

- [ ] **Step 4: Restyle server name panel**

Use `panelClass` for the section. Input should use:

- `bg-gray-800`
- `border-gray-500`
- `rounded-component`
- `focus:border-arix`
- `text-gray-50`
- `placeholder-gray-400`

Keep `maxLength`, `minLength`, and current state updates unchanged.

- [ ] **Step 5: Restyle egg cards**

Keep the current `data.eggs.map` behavior. Change only the visual treatment:

- each egg remains a `<button>`
- selected state uses `selectedCardClass`
- idle state uses `idleCardClass`
- resource chips use Arix gray surfaces and `text-gray-200`
- selected check icon uses `text-arix`

Do not change `selectedEgg` semantics. The selected value remains the FreeServers allowed egg row id.

- [ ] **Step 6: Restyle node cards**

Apply the same card language to node selection:

- selected state uses `border-arix`
- idle state uses gray border/surface
- selected check icon uses `text-arix`

Keep one-node auto-selection unchanged.

- [ ] **Step 7: Restyle sticky summary**

Replace `bg-neutral-800` summary with `panelClass`.

Summary rules:

- labels use `text-gray-300`
- values use `text-gray-50`
- separators use `border-gray-500`
- price/free value uses `text-arix`, not green
- create button uses `bg-arix`, `text-gray-900` or readable white depending on contrast
- disabled button uses secondary/gray styling

Keep `handleCreate`, validation, loading spinner, and redirect unchanged.

- [ ] **Step 8: Remove unused imports**

After restyling, remove any icons no longer used. Keep compile clean.

---

### Task 2: Redesign Dashboard Banner

**Files:**
- Modify: `resources/scripts/blueprint/extensions/freeservers/FreeServersBanner.tsx`

- [ ] **Step 1: Replace promotional green surface**

Change the banner link from `bg-green-*` to an Arix surface:

- `bg-gray-700 backdrop`
- `border border-gray-500`
- `rounded-box`
- hover state that uses `border-arix` or a subtle primary tint

- [ ] **Step 2: Change icon treatment**

Replace the green circular gift treatment with an Arix accent marker:

- either a small `bg-arix` icon square
- or a thin left accent rail using `border-l-2 border-arix`

Keep the existing icon if it still reads clearly, but the overall visual must feel like provisioning capacity rather than a coupon.

- [ ] **Step 3: Improve responsive structure**

Use a layout that stacks on small screens:

- title and message remain visible
- CTA can move below or align right depending on width
- no horizontal overflow

- [ ] **Step 4: Preserve data-driven message**

Keep:

```ts
const bannerMsg = t('bannerMessage')
    .replace('{remaining}', String(data.remaining))
    .replace('{max}', String(data.max_servers));
```

Do not introduce new fake copy.

---

### Task 3: Verify Build And Static Quality

**Files:**
- No expected file edits unless validation exposes issues.

- [ ] **Step 1: Check routes/cache prerequisite**

Run:

```bash
php artisan about
php artisan route:list --path=api/client/extensions/freeservers
```

Expected:

- routes may be cached in production
- FreeServers client API routes should appear before browser verification

If the client API routes are missing, run route cache repair separately before UI testing:

```bash
php artisan route:clear
php artisan route:cache
```

- [ ] **Step 2: Run frontend build**

Run:

```bash
yarn build
```

Expected: webpack build succeeds.

- [ ] **Step 3: Run targeted lint if practical**

Run:

```bash
yarn lint
```

Expected: no new lint errors in the touched FreeServers files. Existing unrelated lint failures should be documented and not fixed in this task.

- [ ] **Step 4: Inspect for unwanted green primary styling**

Run:

```bash
rg -n "green-|text-green|bg-green|border-green" resources/scripts/blueprint/extensions/freeservers/FreeServersPageContent.tsx resources/scripts/blueprint/extensions/freeservers/FreeServersBanner.tsx
```

Expected: no green primary styling remains except deliberate semantic success treatment. If success still uses green utilities, decide whether to replace it with Arix success vars for consistency.

---

### Task 4: Browser Verification

**Files:**
- No expected file edits unless browser verification exposes layout issues.

- [ ] **Step 1: Open dashboard as a user with remaining capacity**

Expected:

- banner appears above server list
- banner matches Arix dark theme
- CTA links to `/account/freeservers`

- [ ] **Step 2: Open `/account/freeservers`**

Expected:

- page loads without API errors
- status header shows real current/max/remaining values
- server name, egg cards, node cards, and summary are visible

- [ ] **Step 3: Exercise selection flow**

Expected:

- selecting an egg changes its selected styling and updates summary
- selecting a node changes its selected styling and updates summary
- create button remains disabled until required values are present

- [ ] **Step 4: Check responsive layout**

Expected:

- mobile viewport stacks sections without horizontal overflow
- sticky summary does not obscure content
- banner remains readable

- [ ] **Step 5: Check unavailable and limit states**

Use test data or temporary API mocking if needed. Do not permanently change production limits just for verification.

Expected:

- unavailable state uses Arix panel styling
- limit reached state uses Arix panel styling
- messages remain readable

---

### Task 5: Final Review

- [ ] **Step 1: Inspect dirty worktree**

Run:

```bash
git status --short
```

Expected: only the intended FreeServers component files are changed by implementation. Existing unrelated dirty files may remain and must not be reverted.

- [ ] **Step 2: Summarize verification**

Report:

- files changed
- build/lint result
- route-cache status if relevant
- browser checks completed
- any residual risks, especially Wings connectivity if an actual create action was attempted

- [ ] **Step 3: Commit when requested**

Only commit if explicitly requested by the user.

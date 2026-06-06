# Design: TOS Tab in Arix Navbar (2026-06-06)

## Overview
Add a "Terms of Service" tab to the main Arix navbar that links to an in-panel page displaying editable TOS content. The content is managed through the existing Arix theme admin editor at `/admin/arix`.

## Goals
- Provide a visible TOS link in the main navbar for all users
- Allow administrators to edit TOS content through the existing Arix settings UI
- Display TOS content on a dedicated page within the panel
- Follow existing Arix patterns (config-driven, no new database tables)

## Non-Goals
- Rich text / WYSIWYG editor (simple textarea is sufficient)
- Version history or audit logging of TOS changes
- Separate admin route for TOS (integrate into existing `/admin/arix`)

## Architecture

### Storage
- TOS content stored as a new key `tos_content` in `config/arix.php`
- Format: raw HTML (admin is trusted; no escaping needed)
- Empty/whitespace value hides the navbar link and can 404 or show a placeholder on `/tos`

### Admin Settings
- New section added to the existing `/admin/arix` form (`resources/views/admin/arix/index.blade.php`)
- Field name: `arix:tos_content`
- UI element: `<textarea>` with label "Terms of Service content"
- Helper text: "Leave empty to hide the TOS link from the navbar"
- Persisted by the existing Arix settings controller using the same `arix:xxx` pattern

### Frontend Routes & Views
- New route: `GET /tos` (registered in `routes/web.php` or an Arix routes file)
- View: `resources/views/arix/tos.blade.php` extending the Arix layout
- The view receives `$tos_content` from config and renders it with `{!! $tos_content !!}`
- Route requires authentication (inherits `auth.session` + 2FA middleware); TOS is only visible to logged-in users (design decision: keep TOS behind login for layout compatibility)

### Navbar Integration
- File: `resources/scripts/components/NavigationBar.tsx`
- Read `arix.tos_content` from the settings store via `useStoreState`
- Conditionally render a link (after the Support link) only when `tos_content` is truthy:
  ```tsx
  {tosContent && <a href="/tos">Terms of Service</a>}
  ```
- The mobile menu automatically includes it via the existing structure or explicit addition if needed

## Data Flow
1. Admin navigates to `/admin/arix`
2. Edits the TOS textarea and submits
3. Controller updates `config/arix.php` with the new value
4. On next page load (or after config cache clear), the navbar reflects the change
5. Clicking the TOS link navigates to `/tos`, which renders the stored content

## Error Handling & Edge Cases
- Empty `tos_content`: Navbar link hidden; `/tos` may return 404 or a simple message
- Config cache enabled: Admins must run `php artisan config:cache` (or clear cache) for changes to appear
- XSS: Not mitigated because only admins can edit; raw HTML output is intentional
- Long content: Textarea should allow large values; no hard length limit imposed

## Testing Considerations
- Verify navbar link appears/disappears based on `tos_content` presence
- Verify `/tos` renders the configured content correctly
- Verify form submission in `/admin/arix` persists the value
- Check mobile menu behavior
- Confirm public access to `/tos` without authentication

## Open Questions
None at this time.

## Dependencies
- Existing Arix admin settings infrastructure
- Existing `NavigationBar.tsx` and Arix Blade layout

## Estimated Scope
Small — touches one config key, one Blade view, one React conditional, and one route. No migrations or new tables.
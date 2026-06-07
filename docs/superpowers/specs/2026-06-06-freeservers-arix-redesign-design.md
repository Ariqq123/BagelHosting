# Free Servers Arix Redesign Design

## Goal

Redesign the Blueprint Free Servers user experience so it feels native to the installed Arix theme while preserving the existing FreeServers API, limits, translations, and creation behavior.

## Scope

- Restyle the account Free Servers page at `/account/freeservers`.
- Restyle the dashboard Free Servers banner shown above the server list.
- Keep the existing API paths, request payloads, and response handling unchanged.
- Keep the existing translated text keys and only adjust layout, visual hierarchy, and component structure.
- Preserve the current server creation flow: user enters a name, selects an egg, selects a node, reviews the summary, and submits.
- Preserve the current unavailable, limit-reached, loading, success, and error states.

Out of scope:

- Changing FreeServers backend authorization or limits.
- Adding billing, queueing, invite codes, CAPTCHA, or anti-abuse controls.
- Changing the admin extension Blade UI.
- Replacing the Arix theme globally.
- Changing global Arix config values.

## Design Direction

Use `frontend-design` with an industrial/utilitarian provisioning console direction adapted to the existing Arix theme.

The interface should feel like creating hosted infrastructure, not claiming a promotional gift. The memorable visual move is a provisioning rail: the main page reads left-to-right from capacity status, through selectable server type and node cards, into a pinned deployment summary.

Visual tokens must come from the current Arix configuration and nearby Arix components:

- surface: `bg-gray-700`, `bg-gray-600`, `bg-gray-800`, `backdrop`
- borders: `border-gray-500`, `border-gray-600`, `border-arix`
- text: `text-gray-50`, `text-gray-100`, `text-gray-200`, `text-gray-300`
- accent: `text-arix`, `bg-arix`, `border-arix`, or `color-mix(in srgb, var(--primary) ...)`
- radius: `rounded-box` for panels, `rounded-component` or existing smaller radius for controls
- semantic feedback: existing Arix success/danger variables when possible

Avoid hardcoded green as the primary brand color. Green may remain only for semantic success messages if it is already part of existing Arix status styling.

## Current State

The installed FreeServers components currently use generic green promotional styling:

- `resources/scripts/blueprint/extensions/freeservers/FreeServersPageContent.tsx`
- `resources/scripts/blueprint/extensions/freeservers/FreeServersBanner.tsx`

The Arix theme uses dark navy surfaces, orange primary accent, rounded cards, and backdrop panels from:

- `config/arix.php`
- `resources/scripts/components/dashboard/ServerCard.tsx`
- `resources/scripts/components/Navigation.tsx`

## User Experience

### Dashboard Banner

The banner should be a compact Arix card, not a bright green promotion.

Required content:

- title from `bannerTitle`
- message from `bannerMessage` with real `remaining` and `max` values
- action from `bannerButton`

Required behavior:

- Hide when the extension is disabled, unavailable, or `remaining <= 0`, as it does today.
- Link to `/account/freeservers`.
- Keep keyboard focus visible.
- Work on narrow mobile layouts without clipped action text.

Visual structure:

- left: icon or compact accent marker
- center: title and remaining copy
- right: CTA with chevron on medium and larger screens
- selected/accent areas use Arix primary, not green

### Account Page

The page should read as a provisioning workflow.

Required sections:

- Status header showing Free Servers title, current usage, max limit, and remaining capacity.
- Server name input.
- Egg selection cards.
- Node selection cards.
- Sticky deployment summary.
- Create button with loading state.

Required state behavior:

- Loading shows existing spinner in an Arix-native empty area.
- Unavailable state shows the API message or translated fallback.
- Limit reached state shows the translated limit reached message.
- Error and success messages remain visible above the workflow.
- When only one node is available, it remains preselected.
- Create remains disabled until `serverName`, `selectedEgg`, and `selectedNode` are all present.
- Successful creation still redirects to `/` after the existing delay.

Visual structure:

- Header panel uses a dark Arix surface with an accent top/bottom rule or side rail.
- Capacity metrics use compact stat chips with tabular-feeling numerals.
- Egg cards display name, nest, description, RAM, disk, and CPU.
- Node cards display node name and location.
- Selected cards use `border-arix` and a subtle primary tint, not green.
- Summary panel uses the same surface language as Arix dashboard cards.
- Create button uses Arix primary and disabled state uses gray/secondary tokens.

## Content Rules

- Do not invent fake server data, fake plan names, fake regions, or fake usage numbers.
- All numbers come from the FreeServers API response.
- Keep standard UI copy from existing translation keys.
- Do not add decorative slogans, themed replacement labels, or filler technical copy.
- Do not remove existing localization support.

## Accessibility

- Selection buttons must remain real buttons.
- Selected state must be visible by more than color where practical, using border weight, check icon, or text treatment.
- Form input retains a visible focus state.
- Disabled create button must be visibly disabled.
- Error/success messages must remain readable against dark surfaces.
- Layout must work at mobile, tablet, and desktop widths.

## Compatibility

- React 16 and TypeScript only.
- No new frontend dependencies.
- Prefer existing `FontAwesomeIcon`, `Spinner`, `PageContentBlock`, Tailwind utilities, and Arix CSS variables.
- Do not introduce `dangerouslySetInnerHTML`.
- Do not change the FreeServers API contract.
- Do not change Blueprint route/component registration files unless build verification proves it is necessary.

## Testing

Minimum verification:

- TypeScript/webpack build succeeds.
- Lint or targeted TypeScript validation succeeds for touched files if the repo supports it.
- Free Servers page can render loading, unavailable, limit reached, selectable workflow, and creating states.
- Dashboard banner renders only when remaining capacity exists.
- No remaining hardcoded primary green styling in the redesigned FreeServers components, except semantic success/error treatment.

Manual browser checks:

- Dashboard with `remaining > 0` shows the banner.
- `/account/freeservers` shows current usage and available server choices.
- Selecting an egg and node updates the summary.
- Disabled create button enables only when required fields are present.
- Mobile layout stacks without horizontal overflow.

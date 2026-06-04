# MC Versions Egg Generator Design

## Goal

Add an admin-only MC Versions generator in the Configuration/Settings area. The generator creates and safely updates one managed Pterodactyl nest named `Minecraft Versions`, with one egg per supported Minecraft server type.

## Scope

The first implementation creates a managed nest and eggs for common server jars such as Paper, Purpur, Fabric, Forge, Velocity, and Vanilla when supported by stable upstream download APIs. It does not create one egg per Minecraft version. Versions are selected at server install time through egg variables.

## Admin UI

Add a Settings subpage at `/admin/settings/mc-versions` and a `MC Versions` tab in the existing Settings navigation. The page shows the managed nest status, supported egg types, last discovery/sync status for each type, and two actions:

- Preview: fetches supported types and reports what would be created or updated.
- Sync Eggs: creates or updates the managed nest and eggs.

The sync action is POST-only, CSRF protected, and available only through the admin routes already protected by the panel's admin middleware.

## Discovery And Downloads

Use MC Utils for catalog/discovery where useful, but use upstream APIs for egg install scripts and actual downloads. This avoids depending on undocumented MC Utils download behavior for installed servers.

Initial upstream strategy:

- Paper: PaperMC API.
- Purpur: Purpur API.
- Vanilla: Mojang version manifest.
- Fabric: Fabric metadata/installer API.
- Forge: Forge metadata/download flow already reflected in the existing Pterodactyl Forge egg pattern.
- Velocity: PaperMC Velocity API.

If a type cannot be resolved safely, preview marks it unavailable and sync skips it instead of creating a broken egg.

## Data Model

No new database tables are required. The generator uses existing `nests`, `eggs`, and `egg_variables` tables.

The managed nest is identified by stable values:

- name: `Minecraft Versions`
- author: `mc-versions-generator`
- description marker: `Managed by MC Versions generator.`

Managed eggs use the same author and a description marker. Sync only updates eggs that match the managed nest and marker. Unrelated nests and eggs are never modified.

## Egg Shape

Each generated egg includes:

- startup command using `SERVER_JARFILE`.
- Docker Java images compatible with modern Minecraft versions.
- install script for the egg type.
- variables for `MINECRAFT_VERSION`, `SERVER_JARFILE`, and type-specific values when needed.

All generated variables remain user-viewable and user-editable unless a variable is a hidden implementation detail such as a direct download URL fallback.

## Idempotency

Sync is idempotent. Re-running it updates managed nest/egg fields and upserts variables by environment variable name. Egg IDs stay stable after the first creation so existing servers that use those eggs are not detached.

The generator does not delete managed eggs in the first version. Removed or unsupported types are shown as skipped to avoid breaking existing servers.

## Error Handling

Preview and sync catch upstream/API failures per type. One failing type does not fail the entire operation unless the managed nest cannot be created or loaded.

The UI reports created, updated, skipped, and failed counts. Failures include short admin-facing reasons without dumping stack traces.

## Implementation Components

- `Admin\Settings\McVersionsController`: renders the page, handles preview and sync.
- `Services\Minecraft\McVersionsCatalogService`: resolves supported server types and upstream availability.
- `Services\Minecraft\McVersionsEggGeneratorService`: creates/updates the managed nest, eggs, and variables.
- Blade view: `resources/views/admin/settings/mc-versions.blade.php`.
- Route additions under `routes/admin.php` in the existing settings group.
- Settings nav addition in `resources/views/partials/admin/settings/nav.blade.php`.

## Testing

Unit or focused feature tests should cover:

- preview returns planned actions without writing records.
- first sync creates one managed nest and supported eggs.
- second sync updates in place and does not duplicate eggs or variables.
- unrelated nests/eggs are not modified.
- a failed upstream type is skipped without failing successful types.

Manual verification should include opening the admin Settings page, previewing, syncing, confirming eggs appear under Nests, and confirming one generated egg has expected startup variables and install script.

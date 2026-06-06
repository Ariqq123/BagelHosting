# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is the Blueprint extension framework for Pterodactyl Panel. It patches Pterodactyl to support installing modular extensions (`.blueprint` files) without manual code changes. The framework consists of a Bash CLI, PHP/Laravel backend additions, React/TypeScript user frontend, and Blade admin frontend.

## Development Commands

### Frontend (React/TypeScript)
- `yarn install` — Install dependencies
- `yarn build` — Development webpack build
- `yarn build:production` — Production build (runs clean first)
- `yarn watch` — Watch mode for development
- `yarn lint` — ESLint on `resources/scripts/**/*.{ts,tsx}`
- `yarn test` — Run Jest tests
- `yarn serve` — Development server with HTTPS (for frontend iteration without full rebuilds)

### Backend (PHP/Laravel)
- `composer cs:fix` — Auto-fix PHP code style
- `composer cs:check` — Check PHP code style (dry-run)
- `php artisan` — Standard Laravel commands

### Blueprint CLI
The `blueprint` command (installed to `/usr/local/bin/blueprint`) runs `blueprint.sh`. Key subcommands: `-install`, `-add`, `-remove`, `-query`, `-build`, `-export`, `-upgrade`, `-debug`.

## Architecture

### CLI Layer (`blueprint.sh`)
- Main entry point sources `scripts/libraries/` and dispatches to `scripts/commands/`
- Sets `BLUEPRINT__FOLDER`, `BLUEPRINT__VERSION`, `BLUEPRINT__DEBUG` environment variables
- Handles Docker detection via `/.dockerenv`
- Supports bash autocompletion when sourced

### Backend Extensions (`app/BlueprintFramework/`)
- Adds `BlueprintFramework` namespace alongside Pterodactyl's `Pterodactyl` namespace
- `Services/PlaceholderService/` handles version placeholder injection
- Extensions are stored in `.blueprint/extensions/`
- Admin UI uses Blade templates in `.blueprint/extensions/blueprint/private/build/`

### Frontend Extensions
- User-facing: React/TypeScript components in `resources/scripts/blueprint/`
- Router extensions in `resources/scripts/blueprint/extends/routers/routes.ts`
- Admin-facing: Blade templates compiled into the panel

### Extension System
- Extensions are `.blueprint` archives containing code, routes, controllers, and assets
- Blueprint web routes are mounted under the `/extensions` prefix
- Installed extensions tracked in `.blueprint/extensions/blueprint/private/db/installed_extensions`
- Build artifacts go to `.blueprint/extensions/blueprint/private/build/`
- The `arix` directory contains a separate theme integration

### Key Environment Files
- `.blueprintrc` — User overrides for ownership, web user, shell (sourced by CLI)
- `config/arix.php` — Arix theme configuration (must be writable by `www-data` for admin settings to save)

### Arix Theme Integration
Arix is a frontend theme for Pterodactyl that integrates with Blueprint. The `arix/` directory contains theme assets (v1.3.1), routes, and migrations. Theme behavior is controlled via `config/arix.php` (colors, layout slots, login layout, mail styling, etc.). Blueprint's React components in `resources/scripts/blueprint/` extend or override Pterodactyl UI to support Arix theming. Registration settings (enabled flag, allowed email domains) are also defined in `config/arix.php`.

**Layout note:** Use `layouts.arix` only for admin editor pages (`/admin/arix/*`). User-facing Arix pages should extend `templates/base/core` (or `templates/wrapper`).
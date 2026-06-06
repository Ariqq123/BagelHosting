---
name: team-launcher
description: Registers the /team slash command to launch debugging swarms, review teams, and research teams. Use when the user runs /team debug, /team review, or /team research.
---

# Team Launcher

Launches specialized AI teams via the `/team` command.

## When to Use

Activate when the user types any of:
- `/team debug`
- `/team review`
- `/team research "<query>"`

Do not activate for any other `/team` usage or general conversation.

## Command Routing

On activation, inspect the first argument after `/team`:

- `debug` → call interactive debug handler
- `review` → call interactive review handler
- `research` → call research handler with remaining arguments as query
- anything else → print short help and exit

If no argument is provided, print help.

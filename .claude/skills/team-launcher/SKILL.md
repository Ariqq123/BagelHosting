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

## Help Message

When an unknown subcommand or no subcommand is given, output exactly:

```
Usage: /team <subcommand>

Subcommands:
  debug      Start interactive debugging swarm
  review     Start interactive review team
  research   Run research team (pass query as argument)

Example:
  /team research "Claude API caching best practices 2026"
```

## Debug Handler (Interactive)

When subcommand is `debug`:

1. Use `AskUserQuestion` to collect:
   - Bug description / symptom
   - Affected files or routes
   - Reproduction steps
   - Any recent changes or error logs

2. Once all answers collected, proceed to spawn debugging swarm (see Agent Orchestration section).
3. User may type "cancel" at any prompt to abort cleanly.

## Review Handler (Interactive)

When subcommand is `review`:

1. Use `AskUserQuestion` to collect:
   - What to review (branch, PR number, file path, or feature area)
   - Focus areas (security, performance, maintainability, tests, or all)
   - Any specific concerns

2. Once context collected, proceed to spawn review team.
3. User may cancel at any prompt.

## Research Handler

When subcommand is `research`:

1. Take the entire remaining argument string as the research query.
2. If no query is provided, print the help message and exit.
3. Immediately delegate to the existing `deep-research` skill with the query.
4. After research completes, write the full report and post chat summary (see Output Handling).

## Error Handling

- Unknown subcommand or missing argument → print help message and exit.
- User types "cancel" during interactive prompts → output "Session cancelled." and exit cleanly.
- All agents return empty results → still write a report noting the failure and suggesting narrower scope or retry.
- Duplicate slug → append `-2`, `-3`, etc. to keep filenames unique.
- Agent failure mid-run → include partial findings and note which agents failed.

## Agent Orchestration

After context is gathered (or query received), the launcher spawns agents using the `Agent` tool:

- Debugging swarm: 4–6 parallel `general-purpose` agents with different lenses + 1 synthesis agent.
- Review team: 3–5 parallel agents (bugs, perf, security, maintainability, tests) + optional judge.
- Research team: delegates to `deep-research` skill.

The launcher decides whether to use simple parallel agent calls or a full `Workflow` based on task complexity.

## Output Handling

After every team run:

1. Generate a slug: `YYYY-MM-DD-<short-description>` (e.g., `2026-06-06-auth-bug-debug`).
2. Write the full report to `docs/superpowers/reports/<slug>.md`.
3. Post a concise summary in chat containing:
   - One-paragraph overview
   - Key findings (bullets)
   - Path to the full report
   - Recommended next actions or blocking issues

If the reports directory does not exist, create it automatically.


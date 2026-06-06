# Team Launcher Design

**Date:** 2026-06-06
**Status:** Approved
**Scope:** Project-specific (Pterodactyl Blueprint repo)

## Overview

Create a reusable `/team` slash command that launches three specialized AI teams on demand:

- **Debugging swarm** — multi-agent root-cause analysis (interactive)
- **Review team** — multi-dimensional code review (interactive)
- **Research team** — deep, verified research reports (argument-driven)

All teams write a full report to `docs/superpowers/reports/<slug>.md` and post a concise summary in chat.

## Command Structure

```
/team debug
/team review
/team research "<query>"
```

- Single skill (`team-launcher`) registers the `/team` command and routes on the first argument.
- Unknown subcommand → short help message.

## Interactive Flow (debug & review)

1. User runs `/team debug` or `/team review`.
2. Launcher collects context via `AskUserQuestion`:
   - Debug: symptom, affected files/routes, reproduction steps, recent changes, logs.
   - Review: target (branch/PR/file/feature), focus areas, specific concerns.
3. Once context gathered, launcher spawns the agent swarm.
4. User may abort at any prompt.

## Research Flow

- `/team research "<query>"` takes the query directly (no prompts).
- Delegates to existing `deep-research` skill pattern.
- Immediately begins fan-out + verification + synthesis.

## Agent Orchestration

- Uses existing `Agent` tool (and `Workflow` for complex runs).
- **Debug swarm**: 4–6 parallel agents (logs, commits, races, config, reproduction, history) + synthesis agent.
- **Review team**: 3–5 parallel agents (bugs, perf, security, maintainability, tests) + optional judge.
- **Research team**: reuses `deep-research` skill.
- Launcher chooses direct `parallel()` vs full `Workflow` based on task complexity.

## Output

- **Full report**: `docs/superpowers/reports/<slug>.md`
  - Slug examples: `2026-06-06-auth-bug-debug`, `2026-06-06-tos-review`
  - Contents: executive summary, findings, evidence, recommendations, agent perspectives.
- **Chat summary**: one-paragraph overview + key findings + report path + next actions.
- **Runtime**: unconstrained (complexity-dependent).

## Directory Layout

- `.claude/skills/team-launcher/SKILL.md` — main launcher skill
- `docs/superpowers/reports/` — generated reports (auto-created)

## Error Handling

- Unknown subcommand → help text
- Interactive abort → clean exit message
- Empty agent results → report noting failure + retry advice
- Missing reports dir → create automatically
- Duplicate slug → append `-2`, `-3`, …
- Mid-run agent failure → partial report + failure note

## Non-Goals

- No hard caps on agent count, tokens, or timeout
- No cross-project portability (project-specific only)
- No new agent types (reuse existing ones)
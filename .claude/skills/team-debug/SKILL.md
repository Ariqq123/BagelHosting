---
name: team-debug
description: Interactive debugging swarm. Use when user types /team-debug.
---

# Team Debug

Launches 4–6 parallel general-purpose agents + 1 synthesis agent for debugging.

## When to Use
Activate on `/team-debug`.

## Flow
1. Use `AskUserQuestion` to collect:
   - Bug description / symptom
   - Affected files or routes
   - Reproduction steps
   - Recent changes or error logs
2. User may type "cancel" at any prompt to abort.
3. Once answers collected, spawn debugging swarm via `Agent` tool.
4. Generate slug `YYYY-MM-DD-<short-desc>-debug`.
5. Write full report to `docs/superpowers/reports/<slug>.md`.
6. Post concise chat summary with overview, findings, report path, next actions.
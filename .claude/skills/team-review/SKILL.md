---
name: team-review
description: Interactive review team. Use when user types /team-review.
---

# Team Review

Launches 3–5 parallel agents (bugs, perf, security, maintainability, tests) + optional judge for code review.

## When to Use
Activate on `/team-review`.

## Flow
1. Use `AskUserQuestion` to collect:
   - What to review (branch, PR, file, or feature area)
   - Focus areas (security, performance, maintainability, tests, or all)
   - Any specific concerns
2. User may type "cancel" at any prompt to abort.
3. Once context collected, spawn review team via `Agent` tool.
4. Generate slug `YYYY-MM-DD-<short-desc>-review`.
5. Write full report to `docs/superpowers/reports/<slug>.md`.
6. Post concise chat summary with overview, findings, report path, next actions.
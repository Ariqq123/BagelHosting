---
name: team-research
description: Research team. Use when user types /team-research "<query>".
---

# Team Research

Delegates to `deep-research` skill and writes report.

## When to Use
Activate on `/team-research "<query>"`.

## Flow
1. Take entire argument string after `/team-research` as query.
2. If no query provided, print: `Usage: /team-research "<query>"`.
3. Delegate to `deep-research` skill with query.
4. After research completes, generate slug `YYYY-MM-DD-<short-desc>-research`.
5. Write full report to `docs/superpowers/reports/<slug>.md`.
6. Post concise chat summary with overview, findings, report path, next actions.

## Error Handling
- No query → print help and exit.
- All agents return empty → write report noting failure, suggest narrower scope.
- Duplicate slug → append `-2`, `-3`, etc.
- Agent failure mid-run → include partial findings, note failed agents.
# Team Launcher Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create a `/team` slash command skill that launches debugging swarms, review teams, and research teams on demand, writing reports to `docs/superpowers/reports/` and posting chat summaries.

**Architecture:** Single skill file (`.claude/skills/team-launcher/SKILL.md`) that registers the `/team` command, routes to subcommand handlers, gathers interactive context when needed, spawns agents via the Agent tool, and produces the required outputs.

**Tech Stack:** Claude Code skills (markdown + frontmatter), `AskUserQuestion` tool for interactive prompts, `Agent` tool for spawning subagents, `Write` tool for report files.

---

### Task 1: Create reports directory

**Files:**
- Create: `docs/superpowers/reports/.gitkeep`

- [ ] **Step 1: Create the reports directory and .gitkeep**

```bash
mkdir -p docs/superpowers/reports
touch docs/superpowers/reports/.gitkeep
```

- [ ] **Step 2: Commit**

```bash
git add docs/superpowers/reports/.gitkeep
git commit -m "chore: add reports directory for team launcher output"
```

### Task 2: Create skill directory structure

**Files:**
- Create: `.claude/skills/team-launcher/SKILL.md`

- [ ] **Step 1: Create the skill directory**

```bash
mkdir -p .claude/skills/team-launcher
```

- [ ] **Step 2: Commit (empty dir will be created by the file in next task)**

```bash
# Directory will be committed when SKILL.md is added
```

### Task 3: Write skill frontmatter and activation rules

**Files:**
- Create: `.claude/skills/team-launcher/SKILL.md` (first 30 lines)

- [ ] **Step 1: Write the frontmatter and "When to Use" section**

```markdown
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
```

- [ ] **Step 2: Run verification command**

```bash
head -20 .claude/skills/team-launcher/SKILL.md
```

Expected: Frontmatter + title + "When to Use" section visible.

- [ ] **Step 3: Commit**

```bash
git add .claude/skills/team-launcher/SKILL.md
git commit -m "feat: add team-launcher skill frontmatter and activation rules"
```

### Task 4: Add command routing skeleton

**Files:**
- Modify: `.claude/skills/team-launcher/SKILL.md` (append after "When to Use")

- [ ] **Step 1: Append the routing section**

```markdown

## Command Routing

On activation, inspect the first argument after `/team`:

- `debug` → call interactive debug handler
- `review` → call interactive review handler
- `research` → call research handler with remaining arguments as query
- anything else → print short help and exit

If no argument is provided, print help.
```

- [ ] **Step 2: Verify the file now contains the routing section**

```bash
grep -A 10 "Command Routing" .claude/skills/team-launcher/SKILL.md
```

Expected: The routing logic text appears.

- [ ] **Step 3: Commit**

```bash
git commit -am "feat: add command routing skeleton to team-launcher"
```

### Task 5: Implement help message

**Files:**
- Modify: `.claude/skills/team-launcher/SKILL.md`

- [ ] **Step 1: Add a `printHelp()` section at the end of the file**

```markdown

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
```

- [ ] **Step 2: Verify the help text is present**

```bash
tail -20 .claude/skills/team-launcher/SKILL.md
```

Expected: The usage block appears at the end.

- [ ] **Step 3: Commit**

```bash
git commit -am "feat: add help message to team-launcher skill"
```

### Task 6: Add interactive debug handler stub

**Files:**
- Modify: `.claude/skills/team-launcher/SKILL.md`

- [ ] **Step 1: Append a debug handler section**

```markdown

## Debug Handler (Interactive)

When subcommand is `debug`:

1. Use `AskUserQuestion` to collect:
   - Bug description / symptom
   - Affected files or routes
   - Reproduction steps
   - Any recent changes or error logs

2. Once all answers collected, proceed to spawn debugging swarm (see Agent Orchestration section).
3. User may type "cancel" at any prompt to abort cleanly.
```

- [ ] **Step 2: Verify the section exists**

```bash
grep -A 5 "Debug Handler" .claude/skills/team-launcher/SKILL.md
```

Expected: Section header and numbered steps visible.

- [ ] **Step 3: Commit**

```bash
git commit -am "feat: add interactive debug handler stub"
```

### Task 7: Add interactive review handler stub

**Files:**
- Modify: `.claude/skills/team-launcher/SKILL.md`

- [ ] **Step 1: Append a review handler section**

```markdown

## Review Handler (Interactive)

When subcommand is `review`:

1. Use `AskUserQuestion` to collect:
   - What to review (branch, PR number, file path, or feature area)
   - Focus areas (security, performance, maintainability, tests, or all)
   - Any specific concerns

2. Once context collected, proceed to spawn review team.
3. User may cancel at any prompt.
```

- [ ] **Step 2: Verify the section**

```bash
grep -A 3 "Review Handler" .claude/skills/team-launcher/SKILL.md
```

Expected: Section header present.

- [ ] **Step 3: Commit**

```bash
git commit -am "feat: add interactive review handler stub"
```

### Task 8: Add research handler

**Files:**
- Modify: `.claude/skills/team-launcher/SKILL.md`

- [ ] **Step 1: Append the research handler**

```markdown

## Research Handler

When subcommand is `research`:

1. Take the entire remaining argument string as the research query.
2. If no query is provided, print the help message and exit.
3. Immediately delegate to the existing `deep-research` skill with the query.
4. After research completes, write the full report and post chat summary (see Output Handling).
```

- [ ] **Step 2: Verify**

```bash
grep -A 3 "Research Handler" .claude/skills/team-launcher/SKILL.md
```

Expected: Section present.

- [ ] **Step 3: Commit**

```bash
git commit -am "feat: add research handler"
```

### Task 9: Add agent orchestration section (high-level)

**Files:**
- Modify: `.claude/skills/team-launcher/SKILL.md`

- [ ] **Step 1: Append the orchestration section**

```markdown

## Agent Orchestration

After context is gathered (or query received), the launcher spawns agents using the `Agent` tool:

- Debugging swarm: 4–6 parallel `general-purpose` agents with different lenses + 1 synthesis agent.
- Review team: 3–5 parallel agents (bugs, perf, security, maintainability, tests) + optional judge.
- Research team: delegates to `deep-research` skill.

The launcher decides whether to use simple parallel agent calls or a full `Workflow` based on task complexity.
```

- [ ] **Step 2: Verify**

```bash
grep -A 2 "Agent Orchestration" .claude/skills/team-launcher/SKILL.md
```

Expected: Section header and first line visible.

- [ ] **Step 3: Commit**

```bash
git commit -am "feat: add agent orchestration section"
```

### Task 10: Add output handling section

**Files:**
- Modify: `.claude/skills/team-launcher/SKILL.md`

- [ ] **Step 1: Append the output section**

```markdown

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
```

- [ ] **Step 2: Verify**

```bash
grep -A 2 "Output Handling" .claude/skills/team-launcher/SKILL.md
```

Expected: Section present.

- [ ] **Step 3: Commit**

```bash
git commit -am "feat: add output handling section"
```

### Task 11: Add error handling section

**Files:**
- Modify: `.claude/skills/team-launcher/SKILL.md`

- [ ] **Step 1: Append the error handling section**

```markdown

## Error Handling

- Unknown subcommand or missing argument → print help message and exit.
- User types "cancel" during interactive prompts → output "Session cancelled." and exit cleanly.
- All agents return empty results → still write a report noting the failure and suggesting narrower scope or retry.
- Duplicate slug → append `-2`, `-3`, etc. to keep filenames unique.
- Agent failure mid-run → include partial findings and note which agents failed.
```

- [ ] **Step 2: Verify**

```bash
grep -A 2 "Error Handling" .claude/skills/team-launcher/SKILL.md
```

Expected: Section present.

- [ ] **Step 3: Commit**

```bash
git commit -am "feat: add error handling section"
```

### Task 12: Final verification and commit

**Files:**
- Modify: `.claude/skills/team-launcher/SKILL.md` (if needed)

- [ ] **Step 1: Check the complete file length and structure**

```bash
wc -l .claude/skills/team-launcher/SKILL.md
head -5 .claude/skills/team-launcher/SKILL.md
tail -10 .claude/skills/team-launcher/SKILL.md
```

Expected: ~80–100 lines, proper frontmatter, all sections present.

- [ ] **Step 2: Final commit**

```bash
git commit -am "feat: complete team-launcher skill implementation"
```

**Plan complete and saved to `docs/superpowers/plans/2026-06-06-team-launcher.md`.**

Two execution options:

**1. Subagent-Driven (recommended)** — I dispatch a fresh subagent per task, review between tasks, fast iteration

**2. Inline Execution** — Execute tasks in this session using executing-plans, batch execution with checkpoints

Which approach?
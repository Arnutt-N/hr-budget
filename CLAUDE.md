# CLAUDE.md

> **All project guidance lives in [`AGENTS.md`](./AGENTS.md). Read that file.**

This repo keeps a single instruction file for every coding agent — Claude Code, Kilo, Codex,
Cursor, and anything else — so guidance can't drift between them. `CLAUDE.md` exists only
because Claude Code looks for it by name.

**Do not add project guidance here.** Put it in `AGENTS.md`. If you find instructions in this
file beyond this pointer, they are stale — trust `AGENTS.md`.

## What's in AGENTS.md

| Section | Covers |
| --- | --- |
| Project Overview | Stack, Phase 6 SPA cutover, what's retired |
| Commands | Two-`package.json` split, `VITE_BASE` build modes, test commands |
| Architecture | Request lifecycle, routing, data layer, views, auth, domain modules, REST API layering, fiscal-year conventions |
| Verification & CI | GitHub Actions is **disabled** — local `composer verify` / `npm run verify` / pre-push hook is the real gate |
| Test environment | `hr_budget_test` isolation guard, `phpunit.xml` env bridging, output-buffer caveat |
| Project layout conventions | `PRPs/`, `project-log-md/`, `docs/agents/`, `archives/`; `research/` is local-only (git-ignored) |
| Conventions | PR title format, Thai/English language rule, retirement-via-git-tags |
| Migration gotchas | No framework, number collisions, rollback pairs |
| Key gotchas | Tracked `public/app/` build artifact, `Router::notFound()` catch-all |
| Agent skills | Issue tracker, triage labels, domain docs — detail in `docs/agents/` |

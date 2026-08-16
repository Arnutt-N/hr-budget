# Domain Docs

How the engineering skills should consume this repo's domain documentation when exploring the
codebase.

## Layout: single-context

```
/
├── CONTEXT.md          ← exists (the domain glossary)
├── docs/adr/           ← does not exist yet
└── src/
```

One glossary and one ADR directory at the repo root. This repo is a single deployable
application (PHP API + Vue SPA over one MySQL schema), not a monorepo — do not introduce
`CONTEXT-MAP.md` or per-context `src/<context>/docs/adr/` directories.

## Before exploring, read these

- **`CONTEXT.md`** at the repo root — the domain glossary.
- **`docs/adr/`** — read ADRs that touch the area you're about to work in.

If either doesn't exist, **proceed silently**. Don't flag their absence; don't suggest
creating them upfront. `/domain-modeling` (reached via `/grill-with-docs` and
`/improve-codebase-architecture`) creates them lazily when terms or decisions actually get
resolved.

## Use the glossary's vocabulary

When your output names a domain concept — an issue title, a refactor proposal, a hypothesis, a
test name — use the term as defined in `CONTEXT.md`. Don't drift to synonyms the glossary
explicitly avoids.

This project's working language is Thai (see the language rule in `AGENTS.md`). Glossary
entries carry both the Thai term and its English identifier counterpart (e.g.
ยอดคงเหลือ / `remaining`); prose and issue titles use the Thai term, code identifiers use the
English one.

If the concept you need isn't in the glossary yet, that's a signal — either you're inventing
language the project doesn't use (reconsider) or there's a real gap (note it for
`/domain-modeling`).

## Flag ADR conflicts

If your output contradicts an existing ADR, surface it explicitly rather than silently
overriding:

> _Contradicts ADR-0007 (event-sourced orders) — but worth reopening because…_

## Seed material

The architecture review at
`project-log-md/claude-code/architecture-review_claude-code-opus-5_2026-08-08_1401.md`
contains an extracted domain glossary (คำของบประมาณ, เบิกจ่าย, ติดตาม, ยอดคงเหลือ,
ปีงบประมาณ, หน่วยงาน, สายอนุมัติ, ขอบเขตสิทธิ์) and a list of candidate ADR topics. Use it as
the starting point when `CONTEXT.md` or the first ADR is created — don't re-derive from
scratch.

Note that `project-log-md/` is git-ignored, so that file is local-only; if it's missing,
proceed without it.

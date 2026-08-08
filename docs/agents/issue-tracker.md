# Issue tracker: GitHub

Issues and PRDs for this repo live as GitHub issues on `Arnutt-N/hr-budget`. Use the `gh` CLI
for all operations — it infers the repo from `git remote -v` when run inside the clone.

## Conventions

- **Create an issue**: `gh issue create --title "..." --body "..."`. Use a heredoc for
  multi-line bodies.
- **Read an issue**: `gh issue view <number> --comments`.
- **List issues**:
  `gh issue list --state open --json number,title,body,labels,comments --jq '[.[] | {number, title, body, labels: [.labels[].name], comments: [.comments[].body]}]'`
  with appropriate `--label` and `--state` filters.
- **Comment on an issue**: `gh issue comment <number> --body "..."`
- **Apply / remove labels**: `gh issue edit <number> --add-label "..."` / `--remove-label "..."`
- **Close**: `gh issue close <number> --comment "..."`

Issue titles and bodies may be written in Thai — that is the working language of this project
(see the language rule in `AGENTS.md`). Keep code identifiers, file paths, and label strings
in English.

## Pull requests as a triage surface

**PRs as a request surface: no.**

This is an internal government HR budgeting system with a single maintainer; it does not
receive external contributions. `/triage` reads issues only and must not pull PRs into the
queue. Flip this to `yes` only if the repo starts accepting outside PRs.

## When a skill says "publish to the issue tracker"

Create a GitHub issue.

## When a skill says "fetch the relevant ticket"

Run `gh issue view <number> --comments`.

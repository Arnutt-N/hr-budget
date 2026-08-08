# Triage Labels

The skills speak in terms of five canonical triage roles. This file maps those roles to the
actual label strings used in this repo's issue tracker.

| Canonical role    | Label in our tracker | Meaning                                  |
| ----------------- | -------------------- | ---------------------------------------- |
| `needs-triage`    | `needs-triage`       | Maintainer needs to evaluate this issue  |
| `needs-info`      | `needs-info`         | Waiting on reporter for more information |
| `ready-for-agent` | `ready-for-agent`    | Fully specified, ready for an AFK agent  |
| `ready-for-human` | `ready-for-human`    | Requires human implementation            |
| `wontfix`         | `wontfix`            | Will not be actioned                     |

No remapping — each role's label string equals its canonical name.

When a skill mentions a role (e.g. "apply the AFK-ready triage label"), use the corresponding
label string from this table.

## Repo state (verified 2026-08-08)

**All five labels exist on `Arnutt-N/hr-budget`.** `/triage` can apply any of them without
further setup.

| Label             | Colour    |
| ----------------- | --------- |
| `needs-triage`    | `#FBCA04` |
| `needs-info`      | `#D4C5F9` |
| `ready-for-agent` | `#0E8A16` |
| `ready-for-human` | `#1D76DB` |
| `wontfix`         | `#ffffff` |

The repo also carries the GitHub defaults (`bug`, `documentation`, `duplicate`,
`enhancement`, `good first issue`, `help wanted`, `invalid`, `question`). Those are
descriptive, not part of the triage state machine — don't treat them as triage states.

If a label is ever deleted, recreate it before the next `/triage` run —
`gh issue edit --add-label` fails on a missing label:

```bash
gh label create needs-triage    --color FBCA04 --description "Maintainer needs to evaluate"
gh label create needs-info      --color D4C5F9 --description "Waiting on reporter"
gh label create ready-for-agent --color 0E8A16 --description "Fully specified, AFK-ready"
gh label create ready-for-human --color 1D76DB --description "Needs human implementation"
```

Edit the right-hand column of the mapping table above if this repo's vocabulary ever diverges.

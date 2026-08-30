---
name: ui_design_system
description: Guide for UI Design System and reusable component library in the HR Budget project.
---

# UI Design System Guide

Standards for consistent and reusable UI in the Vue 3 SPA (`frontend/`).

Stack: **Vue 3 + PrimeVue 4 (Aura preset, dark) + Tailwind 3**, single static dark
theme (`<html class="app-dark">`). Thai for all UI strings. Charts are Chart.js via
`vue-chartjs`.

## 🎨 Design Tokens

No CSS variables — the SPA speaks **Tailwind utility classes + the PrimeVue preset**
(`frontend/src/main.ts`). Key mappings:

| Concept | Class / value |
|:--------|:--------------|
| Page background | body `#0f172a` (`bg-dark-bg`) |
| Card surface | `bg-dark-card` (`#1e293b`) |
| Borders | `border-dark-border` (`#334155`) |
| Muted text | `text-dark-muted` (`#94a3b8`) |
| Primary | Tailwind `sky` scale (`primary-600` = `#0284c7`, see `tailwind.config.js`) |
| **Inline field-error text** | **`text-red-400`** (`#f87171`) — passes WCAG AA on both dark surfaces; never `text-red-500/600` on dark |
| Success/warn/danger surfaces | PrimeVue `severity` props (`success`/`warn`/`danger`) — themed by the preset, don't hand-color |

Font: **Noto Sans Thai** (`frontend/index.html`). Brand mark: Lucide `landmark` on a
`sky-600` rounded square (favicon `frontend/public/favicon.svg`); document title is
Thai-first: "ระบบจัดการงบประมาณทรัพยากรบุคคล (HR Budget)".

## 🧩 Components

### Shared primitives (`frontend/src/components/` + `composables/`)

| Primitive | Replaces | Notes |
|:----------|:---------|:------|
| `PageHeader` | header div `mb-6 flex items-center justify-between` + h1 (+ optional `subtitle` prop) + CTA/back action | title + subtitle props + action slot. Headers holding several filter controls (e.g. Analytics, Budget Execution, Document Vault) stay **inline** (stop-condition) |
| `QueryErrorState` | `<Message severity="error">` load-error banner | `error?: unknown`; Thai fallback "ไม่สามารถโหลดข้อมูลได้" — single owner of that string |
| `ListEmptyState` | DataTable `#empty` `<p class="py-4 text-center text-dark-muted">` | message prop + CTA slot. Richer empty states (icon, multi-line) stay inline |
| `useDeleteConfirm` | the ~15-line delete `confirm.require` block | single owner of header "ยืนยันการลบ" + danger styling. Pages with **distinct** confirm copy (e.g. RoleListPage "ยืนยันลบบทบาท") pass overrides (`header`, custom message) through its options; inline `confirm.require` is reserved for non-delete confirmations (toggles, revokes) |

Never reintroduce a copy of these patterns in a page — import the primitive.

### List surfaces

- All list tables are **PrimeVue DataTable** in the shared card classes
  (`overflow-hidden rounded-lg border border-dark-border shadow`), `data-key="id"`,
  `#empty` → `ListEmptyState`.
- **Admin/master-data pages**: client-side paginator, **`:rows="10"`**.
- **Workflow pages** (`/requests`, `/disbursements`): **lazy** server-side pagination —
  `:lazy`, `:rows="20"` (matches API `PER_PAGE = 20`), `:total-records`,
  `:first="(page-1)*20"`, `@page` mapping PrimeVue's 0-based → API's 1-based.
- Page header CTA: `Button icon="pi pi-plus"` pattern.

### Form-field contract (vee-validate + zod)

Dialect: `useForm({ validationSchema: toTypedSchema(z.object({...})) })` +
`defineField`, per-field Thai errors in
`<small class="text-red-400" role="alert">`. Wiring rules:

1. `InputText` / `Textarea` / native inputs → `<label for="X">` + control `id="X"`.
2. `InputNumber` / `Checkbox` → wrapper `input-id="X"`, label `for="X"`.
3. `Select` → either `<label for="X">` + Select `label-id="X"` (official PrimeVue
   idiom), or `<label id="X">` + Select `aria-labelledby="X"` — never give the
   external label the same id the Select will own.
4. Every error element gets `id="X-error"`; its control gets
   `:aria-describedby="errors.X ? 'X-error' : undefined"`.

`<Message>` in dialogs is for **API-level submit errors only**, not validation.
Modal dialogs = PrimeVue `Dialog` (focus-trapped, Escape handled). Custom
disclosures (dropdown, drawer) must wire `aria-expanded`/`aria-controls`, close on
Escape, and return focus to the trigger — see `NotificationBell.vue`.

### Destructive actions

Always confirm before delete — `useDeleteConfirm`; pass overrides (`header`,
custom message) through its options when copy differs, and reserve inline
`confirm.require` for non-delete confirmations (toggles, revokes). Never delete
immediately. Success/error toasts follow the repo Thai
pattern (`…สำเร็จ` life 3000 / `…ไม่สำเร็จ` life 5000).

### Charts

Chart.js options must honor reduced motion exactly like the five existing charts:
`const reduced = usePrefersReducedMotion()` →
`animation: reduced.value ? false : undefined`.

### Icon-only controls

Thai `aria-label` (and `title` for the tooltip) on every icon-only button/link —
see `DocumentVaultPage` actions, `ItemEditor` row delete.

## ♿ Accessibility baseline

WCAG 2.1 AA (project `accessibility_guidelines` skill is the binding standard —
this doc defers to it on conflict). Everything interactive reachable by Tab;
labels programmatically associated (rules above); focus outlines never removed.

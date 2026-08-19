# Add favicon and Thai page title

Written against: f6badd4

## Evidence chain

- Surface: `frontend/index.html` (the only HTML shell of the SPA)
- Problem: no `<link rel="icon">` anywhere in `<head>` (`index.html:3-13`); `<title>` is the
  English "HR Budget Management" (`index.html:6`) while the entire UI is Thai (`lang="th"`,
  `index.html:2`). Browsers show the default blank icon and an English tab label.
- Design evidence: app identity is Thai-language "ระบบจัดการงบประมาณทรัพยากรบุคคล" (AGENTS.md);
  sidebar brand is the Lucide `Landmark` icon + "HR Budget" (`src/layouts/AppLayout.vue:84-85`).
- Owner: `frontend/index.html`; `frontend/public/` (does not exist yet — Vite serves it at base)
- Scope and affected surfaces: browser tab/bookmark icon and title on every route (SPA shell).
- Uncertainty: none material. No brand asset exists in the repo to reuse, so a simple
  self-drawn SVG favicon is specified; if design later supplies a brand mark, replace the file only.

## Design decision

Ship a minimal SVG favicon matching the app's existing identity (Lucide `landmark` glyph, white
strokes on a sky-600 rounded square) and set a Thai-first title. Smallest change that resolves
both defects without inventing brand intent.

## Reuse

- Glyph: Lucide `landmark` (already shipped in the bundle via `AppLayout.vue:84`)
- Color: `sky-600` `#0284c7` — the app's primary (`tailwind.config.js` primary scale,
  `main.ts:14-30` preset)
- Exemplar: `src/layouts/AppLayout.vue:83-86` (brand block)

## Changes

1. `frontend/public/favicon.svg` (new file; Vite copies `public/` verbatim)
   - Change: create with exactly this content:
     ```svg
     <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
       <rect width="24" height="24" rx="5" fill="#0284c7"/>
       <g fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
         <line x1="3" x2="21" y1="22" y2="22"/>
         <line x1="6" x2="6" y1="18" y2="11"/>
         <line x1="10" x2="10" y1="18" y2="11"/>
         <line x1="14" x2="14" y1="18" y2="11"/>
         <line x1="18" x2="18" y1="18" y2="11"/>
         <polygon points="12 2 20 7 4 7"/>
       </g>
     </svg>
     ```
   - Preserve: nothing (new file).
   - Verify: file renders as a white landmark on a sky rounded square when opened in a browser.
2. `frontend/index.html`
   - Change: inside `<head>`, add `<link rel="icon" type="image/svg+xml" href="/favicon.svg" />`
     and change the title to `ระบบจัดการงบประมาณทรัพยากรบุคคล (HR Budget)`.
   - Preserve: `lang="th"`, `class="app-dark"`, font preconnect/stylesheet, viewport meta.
   - Verify: `cd frontend && npm run build` → `dist/favicon.svg` exists and `dist/index.html`
     contains the new `<link>` and `<title>`.

## Scope

- Inherit: every route (single SPA shell).
- Verify: deploy build too (`VITE_BASE=/hr_budget/public/app/ npm run build`) — the icon href
  `/favicon.svg` is served relative to base; confirm the built `public/app/index.html` resolves.
  If the subdirectory deploy 404s the icon, switch the link to `href="favicon.svg"` (relative).
- Exclude: meta description/OG tags (internal app, no sharing surface); PNG fallbacks; theme-color.

## Validation

- Product: open the app — tab shows the landmark icon and Thai-first title.
- Interface: check both dev (`:5174`) and the Laragon subdirectory deployment.
- System: no other icon link added; no new dependency.
- Repository: `cd frontend && npm run verify` → typecheck + build pass;
  `ls dist/favicon.svg` → exists.

## Stop conditions

- Stop if a real brand asset exists elsewhere in the org — use it instead of the specified SVG.

## Design documentation

- After acceptance: none required. (If a brand mark replaces the placeholder SVG later, record
  it in the project `ui_design_system` skill, which is currently stale.)

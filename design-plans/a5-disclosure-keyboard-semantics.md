# Disclosure semantics: notification dropdown + mobile sidebar (aria-expanded, Escape, focus return)

Written against: f6badd4

## Evidence chain

- Surface: topbar notification bell dropdown and mobile sidebar drawer
- Problems (verified):
  1. `src/components/NotificationBell.vue:62-95` — the trigger button (`:63-76`, has
     `aria-label="การแจ้งเตือน"`) toggles a custom dropdown div (`:82-85`) with no
     `aria-expanded`, no `aria-haspopup`, no `aria-controls`; closing is possible only via the
     click overlay (`:79`); there is **no Escape handler** and focus is not managed.
  2. `src/layouts/AppLayout.vue:70-81` — the mobile sidebar (`aside`, `:78-81`) opens via a
     topbar toggle (the button near `AppLayout.vue:247`, which already carries an `aria-label`)
     and closes via a click-only overlay (`:71-75`); no `aria-expanded`/`aria-controls` wiring,
     no Escape handler.
  3. Codebase-wide grep confirms `aria-expanded`, `aria-haspopup`, `@keydown` = **0 occurrences**
     in `frontend/src` — these two custom disclosures are the only ones needing them (all modals
     use PrimeVue `Dialog`, which traps focus and handles Escape natively).
- Design evidence: project `accessibility_guidelines` skill — keyboard table requires `Escape`
  to close modals/dropdowns; expandable controls pattern (`aria-expanded`/`aria-controls`);
  `fixing-accessibility` rules 2-3 (keyboard access, focus and dialogs — critical); Vercel
  guidelines — Escape closes overlays.
- Owner: `NotificationBell.vue` and `AppLayout.vue` (custom implementations; PrimeVue dialogs
  are already compliant and out of scope).
- Scope and affected surfaces: every authenticated page (both live in AppLayout chrome).
- Uncertainty: none material; the AppLayout toggle button's exact line may drift — locate it by
  its existing `aria-label` (Thai, sidebar toggle) rather than by line number.

## Design decision

Upgrade the two custom disclosures in place: disclose state with `aria-expanded`/`aria-controls`,
close on Escape via a window-level listener active only while open, and return focus to the
trigger on close. No component-library swap (PrimeVue Menu/Drawer would also work but restyling
costs more than these targeted fixes).

## Reuse

- Existing state refs: `open` (`NotificationBell.vue`), `sidebarOpen` (`AppLayout.vue`)
- Existing labels: trigger `aria-label`s already present — keep the strings.
- Idiom: Vue `watch` + `onBeforeUnmount` for listener lifecycle (same idiom used elsewhere in
  the codebase).

## Changes

1. `src/components/NotificationBell.vue`
   - Change:
     - Trigger (`:63-76`): add `:aria-expanded="open"`, `aria-haspopup="true"`,
       `aria-controls="notification-panel"`, and `ref="triggerEl"`.
     - Panel (`:82-85`): add `id="notification-panel"`.
     - Script: add
       ```ts
       const triggerEl = ref<HTMLButtonElement | null>(null)
       function onKeydown(e: KeyboardEvent) {
         if (e.key === 'Escape') close()
       }
       watch(open, (isOpen) => {
         if (isOpen) window.addEventListener('keydown', onKeydown)
         else {
           window.removeEventListener('keydown', onKeydown)
           triggerEl.value?.focus() // return focus to trigger on close
         }
       })
       onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown))
       ```
       (Register the listener only while open; do not steal focus when the dropdown was never
       opened — guard with a `hasOpened` flag if focusing on initial `false` fires.)
   - Preserve: outside-click overlay (`:79`), mark-all-read action, item rendering, badge logic.
   - Verify: Tab to bell → Enter opens; screen reader announces "expanded"; Escape closes and
     focus returns to the bell; clicking a notification still navigates and closes.
2. `src/layouts/AppLayout.vue`
   - Change:
     - Sidebar toggle button (topbar; find by its existing `aria-label`): add
       `:aria-expanded="sidebarOpen"` and `aria-controls="app-sidebar"`.
     - `aside` (`:78-81`): add `id="app-sidebar"`.
     - Script: same Escape pattern keyed on `sidebarOpen` (window listener while open,
       `onBeforeUnmount` cleanup). No focus return needed here — the toggle stays visible and
       tab order is unaffected; add it only if the toggle is unmounted on desktop (it is not:
       it is `lg:hidden`, not `v-if`).
   - Preserve: overlay click-to-close (`:71-75`), `lg:translate-x-0` desktop pinning, route-click
     auto-close (`@click="sidebarOpen = false"` on nav links).
   - Verify: mobile viewport — toggle announces expanded/collapsed; Escape closes; overlay click
     still closes; desktop layout unchanged.

## Scope

- Inherit: all authenticated pages (AppLayout chrome).
- Verify: no other custom overlays exist — confirmed at audit time (only these two plus PrimeVue
  Dialogs); if a new one appears, apply the same pattern rather than copying this plan blindly.
- Exclude: focus **trapping** inside the dropdown (it is a non-modal disclosure, not a dialog —
  trapping would be wrong); PrimeVue `Dialog`/`ConfirmDialog` (already compliant); router
  behavior.

## Validation

- Product: keyboard-only — operate bell and mobile sidebar entirely with Tab/Enter/Escape.
- Interface: 360px-wide viewport for the sidebar case; desktop for the bell case; NVDA or
  Chrome accessibility tree confirms `expanded` state flips.
- System: `grep -rn "aria-expanded" frontend/src` → ≥ 2 hits (bell + sidebar); no new deps.
- Repository: `cd frontend && npm run verify` → passes.

## Stop conditions

- Stop and switch to PrimeVue `Menu`/`Drawer` if product later requires full menu keyboard
  semantics (arrow-key item navigation, typeahead) — that is a component swap, not this plan.

## Design documentation

- After acceptance: none.

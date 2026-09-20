/**
 * Tab-trap for disclosures (mobile drawer, …). Keeps keyboard focus cycling
 * inside `container` while it is open — Tab on the last focusable element
 * wraps to the first, Shift+Tab on the first wraps to the last.
 */
const FOCUSABLE_SELECTOR = [
  'a[href]',
  'button:not([disabled])',
  'input:not([disabled])',
  'select:not([disabled])',
  'textarea:not([disabled])',
  '[tabindex]:not([tabindex="-1"])',
].join(', ')

export function trapTabKey(container: HTMLElement, event: KeyboardEvent): void {
  if (event.key !== 'Tab') return
  const focusable = container.querySelectorAll<HTMLElement>(FOCUSABLE_SELECTOR)
  if (focusable.length === 0) return
  const first = focusable[0]
  const last = focusable[focusable.length - 1]
  const active = document.activeElement
  if (event.shiftKey && active === first) {
    event.preventDefault()
    last.focus()
  } else if (!event.shiftKey && active === last) {
    event.preventDefault()
    first.focus()
  }
}

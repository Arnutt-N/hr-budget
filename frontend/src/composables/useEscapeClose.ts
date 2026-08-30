import { onBeforeUnmount, watch, type Ref } from 'vue'

type CloseFn = () => void

/**
 * Module-level stack of open disclosures (drawer, dropdown, …). One window
 * keydown listener serves the whole stack so a single Escape press closes
 * only the topmost layer — independent per-component listeners would close
 * every open disclosure at once.
 */
const stack: CloseFn[] = []

function onKeydown(e: KeyboardEvent): void {
  if (e.key !== 'Escape' || stack.length === 0) return
  stack[stack.length - 1]()
}

function ensureListener(): void {
  if (stack.length === 1) window.addEventListener('keydown', onKeydown)
}

function releaseListener(): void {
  if (stack.length === 0) window.removeEventListener('keydown', onKeydown)
}

/**
 * Close-on-Escape wiring for a disclosure. While `isOpen` is true its `close`
 * sits on the Escape stack; each Escape press pops (and closes) only the
 * most recently opened disclosure still on the stack.
 */
export function useEscapeClose(isOpen: Ref<boolean>, close: CloseFn): void {
  watch(isOpen, (open) => {
    if (open) {
      stack.push(close)
      ensureListener()
    } else {
      const i = stack.lastIndexOf(close)
      if (i !== -1) stack.splice(i, 1)
      releaseListener()
    }
  })
  onBeforeUnmount(() => {
    const i = stack.lastIndexOf(close)
    if (i !== -1) stack.splice(i, 1)
    releaseListener()
  })
}

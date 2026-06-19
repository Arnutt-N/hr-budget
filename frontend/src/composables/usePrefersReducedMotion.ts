import { onBeforeUnmount, onMounted, ref, type Ref } from 'vue'

/**
 * Reactive `prefers-reduced-motion` flag. Charts feed this into Chart.js
 * `animation: false` so motion-sensitive users get a static render — a cheap
 * a11y win. SSR / no-matchMedia falls back to `false`.
 */
export function usePrefersReducedMotion(): Ref<boolean> {
  const reduced = ref(false)

  if (typeof window === 'undefined' || typeof window.matchMedia !== 'function') {
    return reduced
  }

  const mql = window.matchMedia('(prefers-reduced-motion: reduce)')
  const update = (): void => {
    reduced.value = mql.matches
  }

  onMounted(() => {
    update()
    mql.addEventListener('change', update)
  })
  onBeforeUnmount(() => {
    mql.removeEventListener('change', update)
  })

  return reduced
}

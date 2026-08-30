import { QueryClient } from '@tanstack/vue-query'

/**
 * App-wide TanStack Query client. Exported so logout() can clear the cache —
 * otherwise user-scoped data (notifications, requests, permissions) survives
 * a user switch in the same tab and the next login renders it.
 */
export const queryClient = new QueryClient()

export function buildDocumentTitle(metaTitle: string | undefined, fallback: string): string {
  return metaTitle ? `${metaTitle} · HR Budget` : fallback
}

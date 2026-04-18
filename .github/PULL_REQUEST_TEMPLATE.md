<!-- PR TITLE: <type>: <short description>  (e.g. feat(api): add budget request approval endpoint) -->

## Summary

<!-- 1-3 bullet points: what + why -->

-
-

## Related

<!-- Link to PRD phase / issue / Jira ticket if any -->

- PRD: `.claude/PRPs/prds/...`
- Plan: `.claude/PRPs/plans/...`

## Test Plan

- [ ] `vendor/bin/phpunit --testsuite Unit` passes
- [ ] `cd frontend && npm run typecheck` passes
- [ ] `cd frontend && npm run build` passes
- [ ] Manual: login flow in browser works end-to-end
- [ ] Playwright E2E: `npx playwright test tests/e2e/api`

## Screenshots / Evidence (if UI change)

<!-- Paste before/after screenshots or terminal output -->

## Checklist

- [ ] No hardcoded secrets (JWT_SECRET, DB password, etc.)
- [ ] New endpoints return consistent `ApiResponse` envelope
- [ ] Follows existing naming conventions (PSR-4 for PHP, PascalCase Vue components)
- [ ] Error messages are user-friendly (Thai for UI, English for logs)
- [ ] Tests added for new business logic

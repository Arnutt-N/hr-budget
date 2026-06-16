# Plan: Security Hardening — ThaID id_token + reverse-proxy HTTPS + strict_types

## Summary
A three-part security-hardening batch on the post-cutover surface:
1. **ThaID id_token signature verification** (OIDC defense-in-depth) — config-gated via `THAID_JWKS_URL`; verifies the DOPA-signed id_token against its JWKS and cross-checks `sub` vs userinfo. Dormant unless JWKS is configured (current userinfo-over-TLS behavior preserved).
2. **Reverse-proxy HTTPS detection** — a single `App\Core\Request::isHttps()` that honors `X-Forwarded-Proto: https` when `TRUST_PROXY=true`, used by session cookie, auth cookie, and HSTS so cookies/HSTS are correct behind a TLS-terminating proxy.
3. **`declare(strict_types=1)`** on the remaining `src/Controllers/` files (BudgetExecutionController, FileController).

## Metadata
- **Complexity**: Medium (~10 files; one crypto piece)
- **Source PRD**: N/A — deferred-backlog hardening after the SPA refactor
- **Estimated Files**: ~6 changed + 2 new + 2 tests

## Verified context
- `Router::dispatch` calls controller methods with regex-extracted **string** params; Router has **no** `strict_types`, so it coerces string→int for typed controller params. Therefore adding `strict_types` to the two controllers is safe (coercion is caller-governed; their internal calls already use `(int)`/`(float)` casts).
- `firebase/php-jwt` ships `JWK` + `JWT` (RS256) — no new dependency.
- Current HTTPS checks: `SecurityHeaders::isHttps()` (`$_SERVER['HTTPS']`), `config/app.php` session `secure`, `AuthCookie` (`COOKIE_SECURE` / `APP_ENV==production`).
- `ThaIdProvider::exchangeCode` currently returns the access_token string; `ThaIdAuthService::completeLogin` consumes it.

---

## Files to Change

| File | Action | Why |
|---|---|---|
| `src/Core/Request.php` | CREATE | `isHttps()` honoring X-Forwarded-Proto under `TRUST_PROXY` |
| `src/Core/SecurityHeaders.php` | UPDATE | `isHttps()` → delegate to `Request::isHttps()` |
| `src/Core/AuthCookie.php` | UPDATE | `secure` default → `Request::isHttps()` (env override kept) |
| `config/app.php` | UPDATE | session `secure` default → `Request::isHttps()` |
| `config/auth.php` | UPDATE | add `jwks_url`, `issuer`, `audience` (all env, default '') |
| `src/Services/ThaIdConfig.php` | UPDATE | getters `jwksUrl()`, `issuer()`, `audience()` |
| `src/Services/ThaIdProvider.php` | UPDATE | `exchangeCode` returns `{access_token,id_token}`; add `verifyIdToken()` |
| `src/Services/ThaIdAuthService.php` | UPDATE | verify id_token when JWKS configured + present; cross-check sub |
| `src/Controllers/BudgetExecutionController.php` | UPDATE | add `declare(strict_types=1)` |
| `src/Controllers/FileController.php` | UPDATE | add `declare(strict_types=1)` |
| `tests/Unit/Core/RequestTest.php` | CREATE | isHttps matrix (HTTPS, XFP+trust, XFP no-trust, comma-list) |
| `tests/Unit/Services/ThaIdProviderTest.php` | UPDATE | exchangeCode array shape; verifyIdToken happy + tamper + iss/aud/sub |

## NOT building
- No mandatory id_token (if JWKS configured but DOPA omits id_token → warn + proceed on the TLS userinfo; avoids breaking real logins on an unknown provider behavior).
- No JWKS caching layer (fetch per login; acceptable — login is rare). Could adopt `CachedKeySet` later.
- No change to which routes exist or to password login.

---

## Key designs

### Request::isHttps()
```php
final class Request {
  public static function isHttps(): bool {
    if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') return true;
    if (self::trustProxy()) {
      $xfp = strtolower(trim(explode(',', (string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))[0]));
      if ($xfp === 'https') return true;
    }
    return false;
  }
  private static function trustProxy(): bool {
    return filter_var($_ENV['TRUST_PROXY'] ?? false, FILTER_VALIDATE_BOOLEAN);
  }
}
```
GOTCHA: only trust `X-Forwarded-Proto` when `TRUST_PROXY=true` — otherwise a client could spoof the header to flip `secure`/HSTS. Default off = safe.

### id_token verification (ThaIdProvider)
- `exchangeCode()` → `array{access_token:string, id_token:?string}` (read `id_token` from the token response; null if absent).
- `verifyIdToken(string $idToken): array`:
  - GET `jwks_url` (HttpClient) → `JWK::parseKeySet($jwks)` → `JWT::decode($idToken, $keys)` (RS256; checks signature + exp).
  - if `issuer()` set → assert `iss` matches; if `audience()`/client_id set → assert `aud` contains it.
  - throw generic `id_token_invalid` / `_iss_mismatch` / `_aud_mismatch` (no token body in message).
- `ThaIdAuthService::completeLogin`: after `fetchUserInfo`, if `cfg->jwksUrl() !== ''`:
  - id_token present → `verifyIdToken`; assert `claims['sub'] === identity->sub` else throw `id_token_sub_mismatch`.
  - id_token absent → `error_log('[thaid] WARNING: jwks configured but no id_token returned')`, proceed.

### strict_types
- Prepend `declare(strict_types=1);` after `<?php` in both controllers. Safe per the verified caller-governed coercion. Validate: `php -l` + full unit suite (no regression) + lint.

---

## Testing Strategy
- **RequestTest** (Core): plain HTTPS on/off; XFP=https with TRUST_PROXY true→true, false→false; comma-list `"https, http"` → first wins; HTTPS='off' → false.
- **ThaIdProviderTest** (update): `exchangeCode` returns array incl. id_token; `verifyIdToken` with a test-generated RS256 keypair + JWKS (FakeHttpClient) → claims; tampered signature → throws; wrong iss/aud → throws.
- Regression: full `tests/Unit/{Api,Dtos,Services,Core}` green; `vue-tsc` unaffected (no frontend change); no SPA rebuild needed.

## Validation
```bash
php -l <each changed/new php>
vendor/bin/phpunit tests/Unit/Api tests/Unit/Dtos tests/Unit/Services tests/Unit/Core   # all green
```

## Risks
| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| exchangeCode return-shape change breaks callers/tests | Med | Low | Only caller is completeLogin + ThaIdProviderTest — both updated in this PR |
| strict_types runtime TypeError on a legacy route | Low | Med | Coercion is caller(Router)-governed (no strict); internal calls use casts; `php -l` + suite |
| JWKS verify rejects a valid real DOPA id_token (unknown claim shapes) | Med | Low | Feature is config-gated (off until `THAID_JWKS_URL` set); absent id_token only warns |
| XFP spoofing flips secure/HSTS | Low | Med | Gated behind explicit `TRUST_PROXY=true` |

## Notes
- All three pieces are independently dormant/safe by default: id_token verify off until JWKS configured; XFP trust off until `TRUST_PROXY=true`; strict_types is internal.

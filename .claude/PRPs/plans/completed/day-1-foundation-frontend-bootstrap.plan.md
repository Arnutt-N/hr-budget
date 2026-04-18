# Plan: Day 1 — Foundation + Frontend Bootstrap

## Summary

Scaffold `App\Api\*` REST API layer with JWT auth (using `firebase/php-jwt`) alongside existing MVC, create separate `frontend/` Vue 3 + TypeScript + Pinia + Vue Router SPA, and deliver 1 working end-to-end login flow: Vue login page → `POST /api/v1/auth/login` → store JWT → redirect to dashboard placeholder showing user's name via `GET /api/v1/auth/me`.

## User Story

As a **developer of the hr_budget system**,
I want **a REST API scaffold + Vue SPA shell with working JWT auth**,
So that **subsequent features (Budget Request, Dashboard, etc.) can be built on a consistent, tested foundation without re-deciding architecture every time**.

## Problem → Solution

**Current state**: Custom PHP MVC with session-based auth, server-rendered views, no REST API structure, no Vue, no type safety.

**Desired state**: Coexisting API layer (`App\Api\*`) with JWT-based auth returning JSON + separate Vue 3 SPA in `frontend/` that calls the API and renders reactive UI. Legacy MVC still works untouched (Strangler Fig).

## Metadata

- **Complexity**: **Large** (cross-cutting — adds 2 new subsystems, new libs, new auth scheme)
- **Source PRD**: `.claude/PRPs/prds/hr-budget-rest-api-vue-refactor.prd.md`
- **PRD Phase**: Phase 1 / Day 1 — Foundation + Frontend bootstrap
- **Estimated Files**: ~25 new, 3 updated
- **Time Estimate**: 6-8 hours (Claude Code-assisted)

---

## UX Design

### Before (current state)

```
┌─────────────────────────────────────────────────┐
│  PHP Server (Laragon — hr_budget.test)           │
│                                                   │
│  Browser GET /login → PHP renders HTML form      │
│  User submits → session cookie set              │
│  Redirect → PHP renders dashboard HTML           │
│                                                   │
│  [Full page reload for every interaction]        │
└─────────────────────────────────────────────────┘
```

### After (Day 1 deliverable)

```
┌─────────────────────────────────────────────────┐
│  Vue Dev Server (:5174)                          │
│                                                   │
│  Browser loads / → Vue Router → LoginPage.vue   │
│  User submits → POST http://hr_budget.test/api/v1/auth/login │
│                → 200 { token, user }            │
│  Pinia auth store stores token + user           │
│  Vue Router → DashboardPage.vue (shows name)    │
│                                                   │
│  [SPA — no page reload, JWT in Authorization header]│
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│  PHP Server (Laragon — unchanged for now)        │
│  + NEW: /api/v1/auth/login (JSON endpoint)      │
│  + NEW: /api/v1/auth/me (JSON endpoint)         │
│  [Legacy MVC routes still work]                  │
└─────────────────────────────────────────────────┘
```

### Interaction Changes

| Touchpoint | Before | After | Notes |
|---|---|---|---|
| Login | Form POST + session cookie | JSON fetch + JWT in Authorization header | Legacy form still works (different URL) |
| Page nav | Full reload | Vue Router push | Only new SPA |
| "Who am I?" | `Auth::user()` in PHP | `useAuthStore().user` in Vue | Backed by `/auth/me` |
| Logout | GET /logout → session destroy | Clear Pinia + localStorage | No server-side state to destroy (JWT stateless) |

---

## Mandatory Reading

### Existing codebase (follow these patterns)

| Priority | File | Lines | Why |
|---|---|---|---|
| **P0** | `src/Core/Router.php` | 1-197 | Existing routing — new `/api/v1/*` routes register the same way |
| **P0** | `src/Core/Database.php` | 1-70 | PDO singleton — reuse in new Services/Repositories |
| **P0** | `src/Core/Auth.php` | 1-120 | Existing session auth — we create a parallel JWT-based flow, don't remove |
| **P0** | `src/Models/User.php` | 1-50 | User lookup patterns — `findByEmail`, `find` |
| **P0** | `public/index.php` | 1-46 | Bootstrap sequence — our new Api layer plugs into same flow |
| **P0** | `routes/web.php` | 1-50 | Route registration location + style |
| **P1** | `tests/TestCase.php` | 1-60 | Base test class — extend for new Api tests |
| **P1** | `tests/Unit/BudgetRequestValidationTest.php` | 1-40 | Test style (namespace, `@test` annotation) |
| **P1** | `composer.json` | all | Add `firebase/php-jwt` here |
| **P1** | `.env.example` | all | Add `JWT_SECRET` entry pattern |
| **P1** | `src/Controllers/DisbursementController.php` | 160-180 | Existing raw JSON endpoint (inconsistent pattern we're fixing) |
| **P2** | `src/Core/View.php` | 140-160 | `View::url()` — may need analog for API URLs |
| **P2** | `config/app.php` | all | Config format for new `config/api.php` |
| **P2** | `.htaccess` (root) | all | Ensure rewrite rules don't swallow `/api/v1/*` paths |
| **P2** | `vite.config.js` | all | Existing Vite setup — **do NOT disturb**; create separate `frontend/vite.config.ts` |

### Out-of-repo references (smart-port as style guide — DO NOT copy code)

| Priority | Path | Why |
|---|---|---|
| P2 | `D:\hrProject\smart-port\frontend\src\router\*.js` | Vue Router config pattern (but use TS) |
| P2 | `D:\hrProject\smart-port\frontend\src\stores\auth.js` | Pinia store shape (but use TS + composition) |
| P2 | `D:\hrProject\smart-port\frontend\src\pages\LoginPage.vue` | Login UI layout reference |

---

## External Documentation

| Topic | Source | Key Takeaway |
|---|---|---|
| **firebase/php-jwt v6** | https://github.com/firebase/php-jwt | `JWT::encode($payload, $secret, 'HS256')` + `JWT::decode($token, new Key($secret, 'HS256'))`. `Key` import required for decode. |
| **Vue 3 `<script setup>`** | https://vuejs.org/api/sfc-script-setup.html | Modern SFC syntax; defineProps/Emits macros; all top-level bindings exposed to template |
| **Pinia v3 composition store** | https://pinia.vuejs.org/core-concepts/#setup-stores | `defineStore('id', () => { ... return {state, actions} })` pattern matches smart-port style |
| **Vue Router v4 guards** | https://router.vuejs.org/guide/advanced/navigation-guards.html | `router.beforeEach((to) => { if (requiresAuth && !authStore.isAuthenticated) return { name: 'login' } })` |
| **Tailwind 4 Vite plugin** | https://tailwindcss.com/docs/v4-beta | `@tailwindcss/vite` plugin + `@import "tailwindcss"` in CSS — no `tailwind.config.js` needed at minimum |
| **TypeScript strict mode** | https://www.typescriptlang.org/tsconfig#strict | `"strict": true` enables all strict flags — keep on from day 1 |

### Research Insights

```
KEY_INSIGHT: firebase/php-jwt v6 requires Key object for decode, not raw string
APPLIES_TO: Api/Middleware/AuthMiddleware.php
GOTCHA: JWT::decode($token, $secret, ['HS256']) is v5 signature — v6 uses JWT::decode($token, new Key($secret, 'HS256'))

KEY_INSIGHT: Tailwind 4 beta dropped the config file by default; use CSS-first config
APPLIES_TO: frontend/src/style.css
GOTCHA: If customization needed, use `@theme { ... }` CSS block, not tailwind.config.js

KEY_INSIGHT: Vue Router in SPA mode needs server-side rewrite to index.html (history mode)
APPLIES_TO: frontend dev server (Vite handles it) + later topzlab deploy (.htaccess)
GOTCHA: hash mode works without server config but is uglier URLs. Use history + .htaccess rewrite.

KEY_INSIGHT: Existing root .htaccess rewrites non-existent paths to public/
APPLIES_TO: Deploy planning
GOTCHA: Must update for SPA frontend path OR keep frontend on different subdomain/port during dev
```

---

## Patterns to Mirror

### NAMING_CONVENTION (PHP)
```php
// SOURCE: src/Core/Router.php:10-13
namespace App\Core;

class Router
{
    private static array $routes = [];
```

**Rule**: PSR-4 `App\<Subnamespace>` → `src/<Subnamespace>/`. `App\Api\Controllers\AuthController` → `src/Api/Controllers/AuthController.php`.

### NAMING_CONVENTION (Vue/TS)
```typescript
// TARGET (to create): frontend/src/stores/auth.ts
export const useAuthStore = defineStore('auth', () => { ... })

// TARGET (to create): frontend/src/pages/LoginPage.vue
// PascalCase component files, camelCase stores/composables
```

### ROUTE_REGISTRATION
```php
// SOURCE: routes/web.php:29, 135
Router::get('/api/dashboard/chart-data', [DashboardController::class, 'getChartData']);
Router::get('/api/budget-plans/outputs', [DisbursementController::class, 'getOutputs']);
```

**Rule**: New API routes go in same file, prefix `/api/v1/*`, handler = `[Class, 'method']`.

### DATABASE_ACCESS
```php
// SOURCE: src/Models/User.php:18-28
public static function findByEmail(string $email): ?array
{
    return Database::queryOne(
        "SELECT * FROM " . self::$table . " WHERE email = ?",
        [$email]
    );
}
```

**Rule**: Use `Database::query/queryOne/insert/update` — parameterized always, never string-concat SQL.

### EXISTING_JSON_RESPONSE (inconsistent — DO NOT mirror; use new ApiResponse)
```php
// SOURCE: src/Controllers/DisbursementController.php:163-167 (ANTI-PATTERN)
public function getOutputs()
{
    $parentId = $_GET['parent_id'] ?? 0;
    $outputs = Project::where('plan_id', $parentId)->get();
    echo json_encode($outputs);  // ← no envelope, no status code, no Content-Type header
    exit;
}
```

**NEW PATTERN to establish** (see Task 4 — `ApiResponse` helper):
```php
// TARGET: src/Api/Responses/ApiResponse.php
ApiResponse::ok(['user' => $user, 'token' => $jwt]);  // → 200 {success:true, data:{...}}
ApiResponse::error('Invalid credentials', 401);      // → 401 {success:false, error:'...'}
```

### ERROR_HANDLING (PHP — existing)
```php
// SOURCE: src/Controllers/BudgetRequestController.php:482-487
try {
    $items = \App\Models\BudgetCategoryItem::getHierarchy((int)$categoryId);
    echo json_encode(['success' => true, 'items' => $items]);
} catch (\Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
```

**Rule for Api layer**: wrap controller methods in try/catch, return `ApiResponse::error($e->getMessage(), 500)` (never leak stack traces in production — check `$_ENV['APP_DEBUG']`).

### TEST_STRUCTURE
```php
// SOURCE: tests/Unit/BudgetRequestValidationTest.php:1-20
<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\BudgetRequest;

class BudgetRequestValidationTest extends TestCase
{
    /**
     * @test
     */
    public function request_title_is_required()
    {
        // ...
    }
}
```

**Rule**: extend `Tests\TestCase`, use `@test` annotation, snake_case method names, follow AAA (arrange-act-assert).

### CONFIG_FILE (existing pattern)
```php
// SOURCE: config/app.php:8-15
return [
    'name' => $_ENV['APP_NAME'] ?? 'HR Budget System',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    // ...
];
```

**New config/api.php mirrors this style**:
```php
return [
    'jwt_secret' => $_ENV['JWT_SECRET'] ?? '',
    'jwt_ttl' => (int) ($_ENV['JWT_TTL'] ?? 3600),
    'jwt_algo' => 'HS256',
    'cors_origins' => explode(',', $_ENV['CORS_ORIGINS'] ?? 'http://localhost:5174'),
];
```

---

## Files to Change

### PHP Backend

| File | Action | Justification |
|---|---|---|
| `composer.json` | UPDATE | Add `firebase/php-jwt: ^6.0` dependency |
| `.env.example` | UPDATE | Add `JWT_SECRET=`, `JWT_TTL=3600`, `CORS_ORIGINS=http://localhost:5174` |
| `.env` | UPDATE | Same as above with real values (JWT_SECRET = random 32+ chars) |
| `config/api.php` | CREATE | New config file for API/JWT/CORS settings |
| `src/Core/Jwt.php` | CREATE | Thin wrapper around `firebase/php-jwt` — `Jwt::issue()`, `Jwt::verify()` |
| `src/Api/Responses/ApiResponse.php` | CREATE | `ok()`, `created()`, `error()`, `unauthorized()` static methods — set headers + envelope + exit |
| `src/Api/Middleware/AuthMiddleware.php` | CREATE | Verify `Authorization: Bearer <jwt>`, set current user, reject 401 |
| `src/Api/Middleware/CorsMiddleware.php` | CREATE | Set `Access-Control-Allow-*` headers + handle OPTIONS preflight |
| `src/Api/Controllers/AuthController.php` | CREATE | `login()`, `me()` — JSON handlers |
| `src/Services/AuthService.php` | CREATE | Business logic — password verify, JWT issue, load user |
| `src/Dtos/LoginRequestDto.php` | CREATE | Typed input DTO (email, password) with validation |
| `src/Dtos/AuthResponseDto.php` | CREATE | Typed output DTO (token, user) → `toArray()` |
| `routes/web.php` | UPDATE | Register `/api/v1/auth/login`, `/api/v1/auth/me` |
| `public/index.php` | UPDATE | Apply `CorsMiddleware` for `/api/*` paths BEFORE routing |
| `tests/Unit/Api/ApiResponseTest.php` | CREATE | Unit test for response envelope shape |
| `tests/Unit/Api/JwtTest.php` | CREATE | Unit test for JWT issue/verify roundtrip |
| `tests/Integration/Api/AuthControllerTest.php` | CREATE | Integration test — login with valid/invalid creds, me endpoint with valid/missing token |

### Vue Frontend (new `frontend/` directory)

| File | Action | Justification |
|---|---|---|
| `frontend/package.json` | CREATE | Vue 3, TS, Vite, Pinia, Vue Router, Tailwind deps |
| `frontend/tsconfig.json` | CREATE | TS strict config for Vue |
| `frontend/tsconfig.node.json` | CREATE | TS config for vite.config.ts |
| `frontend/vite.config.ts` | CREATE | Vue plugin, port 5174, dev proxy `/api/*` → backend |
| `frontend/index.html` | CREATE | SPA entry HTML |
| `frontend/src/main.ts` | CREATE | App bootstrap — create Vue app, install Pinia + Router, mount |
| `frontend/src/App.vue` | CREATE | Root component — `<router-view />` |
| `frontend/src/style.css` | CREATE | `@import "tailwindcss";` |
| `frontend/src/router/index.ts` | CREATE | Routes: `/login`, `/` (dashboard) + auth guard |
| `frontend/src/stores/auth.ts` | CREATE | Pinia composition store — `user`, `token`, `login()`, `logout()`, `fetchMe()` |
| `frontend/src/composables/useApi.ts` | CREATE | Fetch wrapper — base URL from env, attach JWT header, handle 401 |
| `frontend/src/types/api.ts` | CREATE | Shared TS types: `ApiResponse<T>`, `User`, `LoginRequest`, `AuthResponse` |
| `frontend/src/pages/LoginPage.vue` | CREATE | Email + password form, call `authStore.login()`, redirect on success |
| `frontend/src/pages/DashboardPage.vue` | CREATE | Minimal: show logged-in user name + logout button |
| `frontend/src/layouts/AppLayout.vue` | CREATE | Shell with header (user name, logout) + `<router-view />` |
| `frontend/.env.example` | CREATE | `VITE_API_URL=http://hr_budget.test` |
| `frontend/.env.development` | CREATE | `VITE_API_URL=http://hr_budget.test` |
| `frontend/.gitignore` | CREATE | `node_modules`, `dist`, `.env.local` |

### Root-level

| File | Action | Justification |
|---|---|---|
| `.gitignore` | UPDATE | Add `frontend/node_modules/`, `frontend/dist/`, `frontend/.env.local` |

## NOT Building (Day 1 scope discipline)

- ❌ **Refresh token / token rotation** — JWT single-token is fine for MVP pilot
- ❌ **Password reset flow** (Day 4)
- ❌ **User CRUD UI** (Day 3)
- ❌ **Rate limiting / brute force protection** — post-MVP
- ❌ **CSRF for JSON API** (stateless JWT = no CSRF risk for same-origin fetch)
- ❌ **Refactoring existing Session-based login** — keep working
- ❌ **Migration of existing PHP views to Vue** — legacy intact (Strangler Fig)
- ❌ **Multiple roles / permissions** (Day 3+)
- ❌ **Production deploy to topzlab** (Day 4)
- ❌ **Email verification on signup** — no signup yet, seed users directly
- ❌ **Avatar upload** (Day 3 file upload)
- ❌ **i18n infrastructure** — Thai-only hardcoded strings OK for Day 1

---

## Step-by-Step Tasks

### Task 1: Install `firebase/php-jwt` + update env config

- **ACTION**: Add `firebase/php-jwt` to composer.json + run `composer update`; add JWT env vars
- **IMPLEMENT**:
  ```json
  // composer.json — in "require" section
  "firebase/php-jwt": "^6.10"
  ```
  ```env
  # .env (and .env.example)
  JWT_SECRET=<generate-with: php -r "echo bin2hex(random_bytes(32));">
  JWT_TTL=3600
  CORS_ORIGINS=http://localhost:5174
  ```
- **MIRROR**: `CONFIG_FILE` pattern (config/app.php)
- **IMPORTS**: N/A (dependency install)
- **GOTCHA**: DO NOT commit real `JWT_SECRET` — `.env` is already gitignored. Commit `.env.example` with placeholder only.
- **VALIDATE**:
  ```bash
  composer update && composer show firebase/php-jwt
  # EXPECT: v6.x installed
  ```

### Task 2: Create `config/api.php`

- **ACTION**: New config file reading from env
- **IMPLEMENT**:
  ```php
  <?php
  return [
      'jwt_secret' => $_ENV['JWT_SECRET'] ?? '',
      'jwt_ttl'    => (int) ($_ENV['JWT_TTL'] ?? 3600),
      'jwt_algo'   => 'HS256',
      'cors_origins' => array_filter(explode(',', $_ENV['CORS_ORIGINS'] ?? 'http://localhost:5174')),
  ];
  ```
- **MIRROR**: `CONFIG_FILE` pattern — `config/app.php` uses `$_ENV` + nullish coalescing
- **IMPORTS**: none
- **GOTCHA**: `filter_var` for booleans if adding bool flags later
- **VALIDATE**: `php -r "print_r(require 'config/api.php');"` — should print array with jwt_secret populated

### Task 3: `src/Core/Jwt.php` — token issue/verify helper

- **ACTION**: Wrap `firebase/php-jwt` with project-friendly static API
- **IMPLEMENT**:
  ```php
  <?php
  namespace App\Core;

  use Firebase\JWT\JWT;
  use Firebase\JWT\Key;
  use Firebase\JWT\ExpiredException;

  class Jwt
  {
      private static ?array $config = null;

      private static function config(): array
      {
          if (self::$config === null) {
              self::$config = require __DIR__ . '/../../config/api.php';
          }
          return self::$config;
      }

      public static function issue(int $userId, array $claims = []): string
      {
          $cfg = self::config();
          $now = time();
          $payload = array_merge([
              'iss' => $_ENV['APP_URL'] ?? 'hr_budget',
              'iat' => $now,
              'exp' => $now + $cfg['jwt_ttl'],
              'sub' => (string) $userId,
          ], $claims);
          return JWT::encode($payload, $cfg['jwt_secret'], $cfg['jwt_algo']);
      }

      /**
       * @return array<string,mixed>|null null if invalid/expired
       */
      public static function verify(string $token): ?array
      {
          $cfg = self::config();
          try {
              $decoded = JWT::decode($token, new Key($cfg['jwt_secret'], $cfg['jwt_algo']));
              return (array) $decoded;
          } catch (ExpiredException $e) {
              return null;
          } catch (\Throwable $e) {
              return null;
          }
      }
  }
  ```
- **MIRROR**: `src/Core/Auth.php` static facade pattern
- **IMPORTS**: `Firebase\JWT\JWT`, `Firebase\JWT\Key`, `Firebase\JWT\ExpiredException`
- **GOTCHA**: v6 requires `new Key($secret, $algo)` — v5 signature `JWT::decode($token, $secret, [$algo])` is DEPRECATED
- **VALIDATE**: See Task 13 (`tests/Unit/Api/JwtTest.php`) — roundtrip test

### Task 4: `src/Api/Responses/ApiResponse.php` — JSON envelope

- **ACTION**: Static helper for consistent API responses
- **IMPLEMENT**:
  ```php
  <?php
  namespace App\Api\Responses;

  class ApiResponse
  {
      public static function ok(mixed $data = null, array $meta = []): never
      {
          self::send(200, ['success' => true, 'data' => $data] + ($meta ? ['meta' => $meta] : []));
      }

      public static function created(mixed $data = null): never
      {
          self::send(201, ['success' => true, 'data' => $data]);
      }

      public static function noContent(): never
      {
          http_response_code(204);
          exit;
      }

      public static function error(string $message, int $status = 400, ?array $details = null): never
      {
          $body = ['success' => false, 'error' => $message];
          if ($details) { $body['details'] = $details; }
          self::send($status, $body);
      }

      public static function unauthorized(string $message = 'Unauthorized'): never
      {
          self::error($message, 401);
      }

      public static function notFound(string $message = 'Not found'): never
      {
          self::error($message, 404);
      }

      private static function send(int $status, array $body): never
      {
          http_response_code($status);
          header('Content-Type: application/json; charset=UTF-8');
          echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
          exit;
      }
  }
  ```
- **MIRROR**: Fix inconsistent `echo json_encode` pattern from `DisbursementController`
- **IMPORTS**: none
- **GOTCHA**: `never` return type (PHP 8.1+) — not `void` — because all paths `exit`
- **VALIDATE**: Task 12 — unit test

### Task 5: `src/Api/Middleware/CorsMiddleware.php`

- **ACTION**: Handle CORS preflight + headers
- **IMPLEMENT**:
  ```php
  <?php
  namespace App\Api\Middleware;

  class CorsMiddleware
  {
      public static function apply(): void
      {
          $cfg = require __DIR__ . '/../../../config/api.php';
          $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
          if (in_array($origin, $cfg['cors_origins'], true)) {
              header("Access-Control-Allow-Origin: $origin");
          }
          header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
          header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
          header('Access-Control-Allow-Credentials: true');
          header('Vary: Origin');

          if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
              http_response_code(204);
              exit;
          }
      }
  }
  ```
- **MIRROR**: smart-port `backend/api.php` CORS approach (but avoid anti-pattern — use allowlist, not wildcard)
- **IMPORTS**: none
- **GOTCHA**: Use `in_array(..., true)` (strict) to avoid `'0' == anything` coercion bugs
- **VALIDATE**: Manual — `curl -X OPTIONS http://hr_budget.test/api/v1/auth/login -H "Origin: http://localhost:5174" -v` → expect `Access-Control-Allow-Origin: http://localhost:5174`

### Task 6: `src/Api/Middleware/AuthMiddleware.php`

- **ACTION**: Verify JWT from Authorization header; 401 if missing/invalid
- **IMPLEMENT**:
  ```php
  <?php
  namespace App\Api\Middleware;

  use App\Core\Jwt;
  use App\Models\User;
  use App\Api\Responses\ApiResponse;

  class AuthMiddleware
  {
      private static ?array $user = null;

      public static function require(): array
      {
          $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
          if (!str_starts_with($header, 'Bearer ')) {
              ApiResponse::unauthorized('Missing Bearer token');
          }
          $token = substr($header, 7);
          $payload = Jwt::verify($token);
          if ($payload === null) {
              ApiResponse::unauthorized('Invalid or expired token');
          }
          $userId = (int) ($payload['sub'] ?? 0);
          $user = User::find($userId);
          if (!$user || !($user['is_active'] ?? true)) {
              ApiResponse::unauthorized('User not found or inactive');
          }
          self::$user = $user;
          return $user;
      }

      public static function user(): ?array { return self::$user; }
  }
  ```
- **MIRROR**: `src/Core/Auth.php::require` pattern (but JSON response)
- **IMPORTS**: `App\Core\Jwt`, `App\Models\User`, `App\Api\Responses\ApiResponse`
- **GOTCHA**: Apache strips `Authorization` header sometimes — fall back to `REDIRECT_HTTP_AUTHORIZATION`. On topzlab may need `.htaccess`: `RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]`
- **VALIDATE**: Task 14 — integration test

### Task 7: `src/Dtos/LoginRequestDto.php`

- **ACTION**: Typed input DTO with validation
- **IMPLEMENT**:
  ```php
  <?php
  namespace App\Dtos;

  final class LoginRequestDto
  {
      public function __construct(
          public readonly string $email,
          public readonly string $password,
      ) {}

      /** @return array<string,string>  map of field → error, empty if valid */
      public function validate(): array
      {
          $errors = [];
          if ($this->email === '') { $errors['email'] = 'Email is required'; }
          elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Invalid email format'; }
          if ($this->password === '') { $errors['password'] = 'Password is required'; }
          return $errors;
      }

      public static function fromRequest(): self
      {
          $raw = json_decode(file_get_contents('php://input') ?: '', true) ?? [];
          return new self(
              email: trim((string) ($raw['email'] ?? '')),
              password: (string) ($raw['password'] ?? ''),
          );
      }
  }
  ```
- **MIRROR**: `readonly` properties, constructor promotion (PHP 8.1+)
- **IMPORTS**: none
- **GOTCHA**: `php://input` empty on non-POST — only call in POST handlers
- **VALIDATE**: Inline — `(new LoginRequestDto('a@b.co', ''))->validate()` returns `['password' => ...]`

### Task 8: `src/Dtos/AuthResponseDto.php`

- **ACTION**: Typed output DTO
- **IMPLEMENT**:
  ```php
  <?php
  namespace App\Dtos;

  final class AuthResponseDto
  {
      public function __construct(
          public readonly string $token,
          public readonly int $expiresIn,
          public readonly array $user,
      ) {}

      public function toArray(): array
      {
          return [
              'token' => $this->token,
              'expires_in' => $this->expiresIn,
              'user' => [
                  'id' => $this->user['id'],
                  'email' => $this->user['email'],
                  'name' => $this->user['name'] ?? '',
                  'role' => $this->user['role'] ?? 'user',
              ],
          ];
      }
  }
  ```
- **MIRROR**: DTO pattern (Task 7)
- **IMPORTS**: none
- **GOTCHA**: Never include `password` hash in response. Whitelist fields in `toArray`.

### Task 9: `src/Services/AuthService.php`

- **ACTION**: Business logic — verify creds, issue JWT
- **IMPLEMENT**:
  ```php
  <?php
  namespace App\Services;

  use App\Core\Jwt;
  use App\Models\User;
  use App\Dtos\AuthResponseDto;

  class AuthService
  {
      /**
       * @return AuthResponseDto|null null on invalid credentials
       */
      public function authenticate(string $email, string $password): ?AuthResponseDto
      {
          $user = User::findByEmail($email);
          if (!$user) { return null; }
          if (!password_verify($password, $user['password'])) { return null; }
          if (array_key_exists('is_active', $user) && !$user['is_active']) { return null; }

          $cfg = require __DIR__ . '/../../config/api.php';
          $token = Jwt::issue((int) $user['id']);
          return new AuthResponseDto($token, $cfg['jwt_ttl'], $user);
      }
  }
  ```
- **MIRROR**: `src/Core/Auth.php::attempt` pattern
- **IMPORTS**: `App\Core\Jwt`, `App\Models\User`, `App\Dtos\AuthResponseDto`
- **GOTCHA**: Use `password_verify` (timing-safe). Do NOT return different error messages for "user not found" vs "wrong password" — prevents user enumeration.

### Task 10: `src/Api/Controllers/AuthController.php`

- **ACTION**: Thin REST handlers
- **IMPLEMENT**:
  ```php
  <?php
  namespace App\Api\Controllers;

  use App\Api\Responses\ApiResponse;
  use App\Api\Middleware\AuthMiddleware;
  use App\Dtos\LoginRequestDto;
  use App\Services\AuthService;

  class AuthController
  {
      public function __construct(private readonly AuthService $service = new AuthService()) {}

      public function login(): never
      {
          $dto = LoginRequestDto::fromRequest();
          $errors = $dto->validate();
          if ($errors) { ApiResponse::error('Validation failed', 422, $errors); }

          $result = $this->service->authenticate($dto->email, $dto->password);
          if (!$result) { ApiResponse::unauthorized('Invalid credentials'); }

          ApiResponse::ok($result->toArray());
      }

      public function me(): never
      {
          $user = AuthMiddleware::require();
          ApiResponse::ok([
              'id' => $user['id'],
              'email' => $user['email'],
              'name' => $user['name'] ?? '',
              'role' => $user['role'] ?? 'user',
          ]);
      }
  }
  ```
- **MIRROR**: Thin controller pattern (from PHP rules)
- **IMPORTS**: `App\Api\Responses\ApiResponse`, `App\Api\Middleware\AuthMiddleware`, `App\Dtos\LoginRequestDto`, `App\Services\AuthService`
- **GOTCHA**: `new AuthService()` as default parameter works in PHP 8.1+ (not earlier). DI container comes later.

### Task 11: Register API routes + apply CORS in bootstrap

- **ACTION**: Update `routes/web.php` and `public/index.php`
- **IMPLEMENT** — `routes/web.php` add near top:
  ```php
  use App\Api\Controllers\AuthController as ApiAuthController;

  // API v1 Routes
  Router::post('/api/v1/auth/login', [ApiAuthController::class, 'login']);
  Router::get('/api/v1/auth/me', [ApiAuthController::class, 'me']);
  Router::get('/api/v1/health', function () {
      \App\Api\Responses\ApiResponse::ok(['version' => '0.1.0', 'time' => date('c')]);
  });
  ```
  
  **`public/index.php`** — add after `Dotenv::safeLoad()` but before `Router::dispatch()`:
  ```php
  // Apply CORS for API routes
  if (str_starts_with(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '', '/hr_budget/public/api/')
      || str_starts_with(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '', '/api/')) {
      \App\Api\Middleware\CorsMiddleware::apply();
  }
  ```
- **MIRROR**: `routes/web.php:29` — same `Router::get(...)` registration style
- **IMPORTS**: shown above
- **GOTCHA**: `public/index.php` check both `/api/` (domain root) and `/hr_budget/public/api/` (subdirectory deploy) — existing router handles script-prefix, replicate here
- **VALIDATE**:
  ```bash
  curl http://hr_budget.test/api/v1/health
  # EXPECT: {"success":true,"data":{"version":"0.1.0","time":"..."}}
  ```

### Task 12: Test — `tests/Unit/Api/ApiResponseTest.php`

- **ACTION**: Verify envelope shape + status codes
- **IMPLEMENT**: Use output buffering since `ApiResponse::*` exits.
  ```php
  <?php
  namespace Tests\Unit\Api;

  use Tests\TestCase;
  use App\Api\Responses\ApiResponse;

  class ApiResponseTest extends TestCase
  {
      /** @test */
      public function ok_returns_envelope_with_data()
      {
          $this->expectExitCode(200);
          ob_start();
          try {
              ApiResponse::ok(['foo' => 'bar']);
          } catch (\Exception $e) {
              // exit is trapped in phpunit via @runInSeparateProcess
          }
          $out = ob_get_clean();
          $json = json_decode($out, true);
          $this->assertTrue($json['success']);
          $this->assertSame(['foo' => 'bar'], $json['data']);
      }
  }
  ```
- **MIRROR**: `tests/Unit/BudgetRequestValidationTest.php` AAA style
- **IMPORTS**: `Tests\TestCase`, `App\Api\Responses\ApiResponse`
- **GOTCHA**: `exit` in test = process dies. Use `@runInSeparateProcess` annotation OR refactor `ApiResponse::send` to accept `bool $exit = true` (testable). **Decision**: add `$exit` param to `send()` — default `true`, set `false` in tests.
- **VALIDATE**: `vendor/bin/phpunit tests/Unit/Api/ApiResponseTest.php`

### Task 13: Test — `tests/Unit/Api/JwtTest.php`

- **ACTION**: Roundtrip issue + verify + expired
- **IMPLEMENT**:
  ```php
  <?php
  namespace Tests\Unit\Api;

  use Tests\TestCase;
  use App\Core\Jwt;

  class JwtTest extends TestCase
  {
      /** @test */
      public function issue_and_verify_roundtrip()
      {
          $token = Jwt::issue(userId: 42, claims: ['role' => 'admin']);
          $this->assertIsString($token);
          $payload = Jwt::verify($token);
          $this->assertNotNull($payload);
          $this->assertSame('42', $payload['sub']);
          $this->assertSame('admin', $payload['role']);
      }

      /** @test */
      public function verify_returns_null_for_garbage()
      {
          $this->assertNull(Jwt::verify('not-a-jwt'));
      }
  }
  ```
- **MIRROR**: `tests/Unit/BudgetRequestValidationTest.php`
- **IMPORTS**: `Tests\TestCase`, `App\Core\Jwt`
- **GOTCHA**: Tests need `JWT_SECRET` — set in `phpunit.xml` `<env>` block or `tests/bootstrap.php`
- **VALIDATE**: `vendor/bin/phpunit tests/Unit/Api/JwtTest.php`

### Task 14: Test — `tests/Integration/Api/AuthControllerTest.php`

- **ACTION**: End-to-end login against real DB
- **IMPLEMENT**: Seed a user, call login endpoint via simulated request, assert JSON response.
  ```php
  <?php
  namespace Tests\Integration\Api;

  use Tests\TestCase;
  use App\Services\AuthService;

  class AuthControllerTest extends TestCase
  {
      /** @test */
      public function authenticate_succeeds_with_valid_creds()
      {
          $user = $this->createUser(['password' => password_hash('pass1234', PASSWORD_DEFAULT)]);
          $result = (new AuthService())->authenticate($user['email'], 'pass1234');
          $this->assertNotNull($result);
          $this->assertIsString($result->token);
      }

      /** @test */
      public function authenticate_fails_with_wrong_password()
      {
          $user = $this->createUser();
          $result = (new AuthService())->authenticate($user['email'], 'wrong');
          $this->assertNull($result);
      }
  }
  ```
- **MIRROR**: `tests/Integration/BudgetRequestSecurityTest.php`
- **IMPORTS**: `Tests\TestCase`, `App\Services\AuthService`
- **GOTCHA**: `createUser()` helper must exist in `Tests\TestCase` — verify or add
- **VALIDATE**: `vendor/bin/phpunit --testsuite Integration`

---

### Task 15: `frontend/package.json` + install

- **ACTION**: Create separate Node project for Vue SPA
- **IMPLEMENT**:
  ```json
  {
    "name": "hr-budget-frontend",
    "private": true,
    "version": "0.1.0",
    "type": "module",
    "scripts": {
      "dev": "vite --host 0.0.0.0 --port 5174",
      "build": "vue-tsc -b && vite build",
      "preview": "vite preview",
      "typecheck": "vue-tsc --noEmit"
    },
    "dependencies": {
      "pinia": "^3.0.0",
      "vue": "^3.5.0",
      "vue-router": "^4.5.0"
    },
    "devDependencies": {
      "@tailwindcss/vite": "^4.1.0",
      "@vitejs/plugin-vue": "^5.2.0",
      "@vue/tsconfig": "^0.7.0",
      "tailwindcss": "^4.1.0",
      "typescript": "^5.6.0",
      "vite": "^6.0.0",
      "vue-tsc": "^2.1.0"
    }
  }
  ```
  Then `cd frontend && npm install`.
- **MIRROR**: `D:\hrProject\smart-port\frontend\package.json` (reference) — but use TS everywhere
- **IMPORTS**: none
- **GOTCHA**: Do NOT install in repo root — keep `frontend/node_modules/` separate from existing `node_modules/` (legacy Vite)
- **VALIDATE**: `cd frontend && npm install && npm run dev` → dev server starts on :5174

### Task 16: `frontend/vite.config.ts` + `tsconfig.json` + `index.html`

- **ACTION**: TS Vite config with Vue plugin + API proxy
- **IMPLEMENT** — `vite.config.ts`:
  ```typescript
  import { defineConfig, loadEnv } from 'vite'
  import vue from '@vitejs/plugin-vue'
  import tailwindcss from '@tailwindcss/vite'

  export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '')
    return {
      plugins: [vue(), tailwindcss()],
      server: {
        port: 5174,
        strictPort: true,
        proxy: {
          '/api': {
            target: env.VITE_API_URL || 'http://hr_budget.test',
            changeOrigin: true,
          },
        },
      },
      resolve: { alias: { '@': '/src' } },
    }
  })
  ```
  `tsconfig.json` — extends `@vue/tsconfig/tsconfig.dom.json`, include `src`.
  `index.html` — `<div id="app"></div>` + `<script type="module" src="/src/main.ts">`.
- **MIRROR**: smart-port `vite.config.js` (but TS)
- **IMPORTS**: shown above
- **GOTCHA**: Vite proxy requires `/api` to NOT have trailing slash in rule; strict port avoids silent switch if 5174 busy.
- **VALIDATE**: `npm run dev` and open `http://localhost:5174` — blank Vue app

### Task 17: `frontend/src/types/api.ts`

- **ACTION**: Shared TS types mirroring backend
- **IMPLEMENT**:
  ```typescript
  export interface ApiResponse<T> {
    success: boolean
    data?: T
    error?: string
    details?: Record<string, string>
    meta?: Record<string, unknown>
  }

  export interface User {
    id: number
    email: string
    name: string
    role: string
  }

  export interface LoginRequest {
    email: string
    password: string
  }

  export interface AuthResponse {
    token: string
    expires_in: number
    user: User
  }
  ```
- **MIRROR**: Standard TS type declarations
- **IMPORTS**: none
- **GOTCHA**: `snake_case` field names match backend DTOs (`expires_in`). Don't silently rename — keep contract explicit.

### Task 18: `frontend/src/composables/useApi.ts`

- **ACTION**: Fetch wrapper with JWT injection
- **IMPLEMENT**:
  ```typescript
  import { useAuthStore } from '@/stores/auth'
  import type { ApiResponse } from '@/types/api'

  const BASE = import.meta.env.VITE_API_URL || ''

  export async function apiFetch<T = unknown>(
    path: string,
    options: RequestInit = {},
  ): Promise<ApiResponse<T>> {
    const auth = useAuthStore()
    const headers = new Headers(options.headers)
    headers.set('Content-Type', 'application/json')
    if (auth.token) headers.set('Authorization', `Bearer ${auth.token}`)

    const res = await fetch(`${BASE}/api/v1${path}`, { ...options, headers })
    if (res.status === 401) {
      auth.logout()  // clear and redirect
    }
    return res.json() as Promise<ApiResponse<T>>
  }

  export const useApi = () => ({ apiFetch })
  ```
- **MIRROR**: smart-port `axios` pattern (but use native fetch for zero-dep)
- **IMPORTS**: Pinia store, types
- **GOTCHA**: `useAuthStore()` must be called INSIDE function (after Pinia installed on app), not at module top-level

### Task 19: `frontend/src/stores/auth.ts`

- **ACTION**: Pinia composition store for auth state
- **IMPLEMENT**:
  ```typescript
  import { defineStore } from 'pinia'
  import { ref, computed } from 'vue'
  import type { User, LoginRequest, AuthResponse, ApiResponse } from '@/types/api'

  const TOKEN_KEY = 'hr_budget_token'
  const USER_KEY = 'hr_budget_user'
  const BASE = import.meta.env.VITE_API_URL || ''

  export const useAuthStore = defineStore('auth', () => {
    const token = ref<string>(localStorage.getItem(TOKEN_KEY) ?? '')
    const user = ref<User | null>(
      (() => {
        const raw = localStorage.getItem(USER_KEY)
        try { return raw ? JSON.parse(raw) as User : null } catch { return null }
      })()
    )

    const isAuthenticated = computed(() => token.value !== '')

    async function login(req: LoginRequest): Promise<{ ok: boolean; error?: string }> {
      const res = await fetch(`${BASE}/api/v1/auth/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(req),
      })
      const json = (await res.json()) as ApiResponse<AuthResponse>
      if (!json.success || !json.data) {
        return { ok: false, error: json.error ?? 'Unknown error' }
      }
      token.value = json.data.token
      user.value = json.data.user
      localStorage.setItem(TOKEN_KEY, token.value)
      localStorage.setItem(USER_KEY, JSON.stringify(user.value))
      return { ok: true }
    }

    function logout() {
      token.value = ''
      user.value = null
      localStorage.removeItem(TOKEN_KEY)
      localStorage.removeItem(USER_KEY)
    }

    return { token, user, isAuthenticated, login, logout }
  })
  ```
- **MIRROR**: smart-port `stores/auth.js` composition pattern (but TS + typed)
- **IMPORTS**: Pinia, Vue refs, types
- **GOTCHA**: JSON parse of localStorage can throw on corrupt data — wrap in try/catch (shown). Never assume localStorage is clean.

### Task 20: `frontend/src/router/index.ts` with auth guard

- **ACTION**: Routes + guard redirecting unauth → login
- **IMPLEMENT**:
  ```typescript
  import { createRouter, createWebHistory } from 'vue-router'
  import { useAuthStore } from '@/stores/auth'

  const router = createRouter({
    history: createWebHistory(),
    routes: [
      { path: '/login', name: 'login', component: () => import('@/pages/LoginPage.vue'), meta: { requiresAuth: false } },
      {
        path: '/',
        component: () => import('@/layouts/AppLayout.vue'),
        meta: { requiresAuth: true },
        children: [
          { path: '', redirect: '/dashboard' },
          { path: 'dashboard', name: 'dashboard', component: () => import('@/pages/DashboardPage.vue') },
        ],
      },
    ],
  })

  router.beforeEach((to) => {
    const auth = useAuthStore()
    const requiresAuth = to.matched.some(r => r.meta.requiresAuth)
    if (requiresAuth && !auth.isAuthenticated) return { name: 'login', query: { redirect: to.fullPath } }
    if (to.name === 'login' && auth.isAuthenticated) return { name: 'dashboard' }
  })

  export default router
  ```
- **MIRROR**: smart-port `router/index.js`
- **IMPORTS**: Vue Router, Pinia store
- **GOTCHA**: `createWebHistory()` needs server rewrite — fine for Vite dev, needs `.htaccess` for topzlab later (Day 4)

### Task 21: `main.ts`, `App.vue`, `style.css`

- **ACTION**: Bootstrap files
- **IMPLEMENT**:
  ```typescript
  // main.ts
  import { createApp } from 'vue'
  import { createPinia } from 'pinia'
  import router from './router'
  import App from './App.vue'
  import './style.css'

  const app = createApp(App)
  app.use(createPinia())
  app.use(router)
  app.mount('#app')
  ```
  ```vue
  <!-- App.vue -->
  <script setup lang="ts"></script>
  <template><RouterView /></template>
  ```
  ```css
  /* style.css */
  @import "tailwindcss";
  ```
- **MIRROR**: smart-port `main.js` + Vue 3 docs
- **IMPORTS**: shown above
- **GOTCHA**: Pinia must be installed BEFORE router (router guard uses store)

### Task 22: `pages/LoginPage.vue`

- **ACTION**: Email + password form
- **IMPLEMENT**:
  ```vue
  <script setup lang="ts">
  import { ref } from 'vue'
  import { useRouter, useRoute } from 'vue-router'
  import { useAuthStore } from '@/stores/auth'

  const router = useRouter()
  const route = useRoute()
  const auth = useAuthStore()

  const email = ref('')
  const password = ref('')
  const error = ref('')
  const loading = ref(false)

  async function onSubmit() {
    error.value = ''
    loading.value = true
    const result = await auth.login({ email: email.value, password: password.value })
    loading.value = false
    if (!result.ok) { error.value = result.error ?? 'เข้าสู่ระบบไม่สำเร็จ'; return }
    const redirect = (route.query.redirect as string) || '/dashboard'
    router.replace(redirect)
  }
  </script>

  <template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
      <form @submit.prevent="onSubmit" class="bg-white p-8 rounded-lg shadow-md w-full max-w-md space-y-4">
        <h1 class="text-2xl font-bold text-gray-900">เข้าสู่ระบบ</h1>
        <input v-model="email" type="email" placeholder="อีเมล" required
               class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500" />
        <input v-model="password" type="password" placeholder="รหัสผ่าน" required
               class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500" />
        <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
        <button type="submit" :disabled="loading"
                class="w-full py-2 bg-blue-600 text-white rounded font-semibold disabled:opacity-50">
          {{ loading ? 'กำลังเข้าสู่ระบบ...' : 'เข้าสู่ระบบ' }}
        </button>
      </form>
    </div>
  </template>
  ```
- **MIRROR**: smart-port `LoginPage.vue` structure
- **IMPORTS**: Vue + Vue Router + auth store
- **GOTCHA**: `redirect` query param as string (not array) — cast explicitly

### Task 23: `layouts/AppLayout.vue` + `pages/DashboardPage.vue`

- **ACTION**: Minimal authenticated shell
- **IMPLEMENT** — `AppLayout.vue`:
  ```vue
  <script setup lang="ts">
  import { useRouter } from 'vue-router'
  import { useAuthStore } from '@/stores/auth'

  const router = useRouter()
  const auth = useAuthStore()

  function onLogout() {
    auth.logout()
    router.replace('/login')
  }
  </script>

  <template>
    <div class="min-h-screen bg-gray-100">
      <header class="bg-white shadow-sm px-6 py-3 flex justify-between items-center">
        <h1 class="font-bold text-gray-900">HR Budget</h1>
        <div class="flex items-center gap-4">
          <span class="text-sm text-gray-700">{{ auth.user?.name }}</span>
          <button @click="onLogout" class="text-sm text-red-600">ออกจากระบบ</button>
        </div>
      </header>
      <main class="p-6"><RouterView /></main>
    </div>
  </template>
  ```
  — `DashboardPage.vue`:
  ```vue
  <script setup lang="ts">
  import { useAuthStore } from '@/stores/auth'
  const auth = useAuthStore()
  </script>

  <template>
    <div class="bg-white p-6 rounded-lg shadow-sm">
      <h2 class="text-xl font-bold mb-2">Dashboard</h2>
      <p>สวัสดี, <strong>{{ auth.user?.name }}</strong>!</p>
      <p class="text-sm text-gray-500 mt-2">Email: {{ auth.user?.email }}</p>
      <p class="text-xs text-gray-400 mt-4">Day 1 foundation — placeholder</p>
    </div>
  </template>
  ```
- **MIRROR**: smart-port `AppLayout.vue`, `DashboardPage.vue`
- **IMPORTS**: Vue Router + auth store
- **GOTCHA**: `auth.user?.name` — optional chaining since user may be null briefly on first render

### Task 24: `.gitignore` updates + `frontend/.env.example`

- **ACTION**: Exclude frontend build artifacts
- **IMPLEMENT** — root `.gitignore` append:
  ```
  # Frontend SPA (new)
  /frontend/node_modules/
  /frontend/dist/
  /frontend/.env.local
  /frontend/.env.*.local
  ```
  — `frontend/.env.example`:
  ```
  VITE_API_URL=http://hr_budget.test
  ```
- **MIRROR**: existing `.gitignore` structure (section comments)
- **IMPORTS**: none
- **GOTCHA**: Do NOT gitignore `frontend/.env.example` (use `!` negation? — not needed, the `/frontend/.env.*.local` pattern doesn't match `.env.example`)

### Task 25: Integration smoke test — end-to-end login

- **ACTION**: Manual browser test of full flow
- **IMPLEMENT**: (manual steps, not code)
  1. Ensure user exists in `hr_budget` DB:
     ```sql
     INSERT INTO users (email, name, password, role, is_active)
     VALUES ('test@hr.mojo', 'Test User', '$2y$10$<hash-of-pass1234>', 'user', 1);
     ```
     Generate hash: `php -r "echo password_hash('pass1234', PASSWORD_DEFAULT);"`
  2. Start backend: Laragon running (Apache + MySQL)
  3. Start frontend: `cd frontend && npm run dev`
  4. Browser: `http://localhost:5174` → redirects to `/login`
  5. Submit `test@hr.mojo` / `pass1234`
  6. Should: see dashboard with "Test User" displayed
  7. Refresh page → still logged in (localStorage)
  8. Click "ออกจากระบบ" → back to login
- **MIRROR**: manual QA
- **IMPORTS**: N/A
- **GOTCHA**: CORS will bite if origin mismatch — verify Vite proxy OR `CORS_ORIGINS` env value
- **VALIDATE**: all 8 steps pass

---

## Testing Strategy

### Unit Tests

| Test | Input | Expected | Edge Case? |
|---|---|---|---|
| `ApiResponseTest::ok_returns_envelope_with_data` | `ApiResponse::ok(['x' => 1])` | JSON `{success:true,data:{x:1}}`, status 200 | — |
| `ApiResponseTest::error_returns_correct_status` | `ApiResponse::error('x', 422)` | status 422, `{success:false,error:'x'}` | — |
| `JwtTest::issue_and_verify_roundtrip` | `Jwt::issue(42)` → `Jwt::verify($token)` | payload has `sub='42'` | — |
| `JwtTest::verify_returns_null_for_garbage` | `Jwt::verify('junk')` | null | Yes — invalid format |
| `JwtTest::verify_returns_null_for_expired` | expired token | null | Yes — expiry |
| `LoginRequestDtoTest::validates_email_format` | `new Dto('notanemail', 'pw')` | error with `email` key | Yes — invalid input |

### Integration Tests

| Test | Input | Expected |
|---|---|---|
| `AuthControllerTest::authenticate_succeeds_with_valid_creds` | seed user + correct password | `AuthResponseDto` not null, token is string |
| `AuthControllerTest::authenticate_fails_with_wrong_password` | seed user + wrong password | null |
| `AuthControllerTest::authenticate_fails_for_inactive_user` | seed inactive user | null (edge — is_active flag) |

### Edge Cases Checklist

- [ ] Empty email/password → 422 with field errors
- [ ] Malformed JSON body → 400 (check)
- [ ] Missing Authorization header on `/me` → 401
- [ ] Invalid/expired JWT on `/me` → 401
- [ ] User deleted but valid JWT still presented → 401 ("User not found")
- [ ] User `is_active=false` → login + me both refuse
- [ ] CORS preflight OPTIONS → 204 with correct headers
- [ ] Unknown origin in `Origin` header → no ACAO header returned

---

## Validation Commands

### Static Analysis / Build

```bash
# PHP syntax sanity
find src/Api src/Services src/Dtos src/Core/Jwt.php -name '*.php' -exec php -l {} \;
# EXPECT: "No syntax errors detected" for each

# Composer autoload regenerate
composer dump-autoload
# EXPECT: "Generated optimized autoload files"
```

### Unit Tests

```bash
vendor/bin/phpunit --testsuite Unit
# EXPECT: All tests pass, 0 failures
```

### Integration Tests

```bash
vendor/bin/phpunit --testsuite Integration
# EXPECT: All tests pass, DB hr_budget_test reachable
```

### Full PHP Test Suite

```bash
vendor/bin/phpunit
# EXPECT: Combined Unit + Integration pass
```

### Frontend Typecheck + Build

```bash
cd frontend && npm run typecheck
# EXPECT: No TS errors

cd frontend && npm run build
# EXPECT: dist/ generated, no errors
```

### Browser Validation

```bash
# Terminal 1 — backend (Laragon already running, verify)
curl http://hr_budget.test/api/v1/health
# EXPECT: {"success":true,"data":{"version":"0.1.0",...}}

# Terminal 2 — frontend dev
cd frontend && npm run dev

# Browser: http://localhost:5174 → login → dashboard
```

### Manual Validation

- [ ] `curl -X POST http://hr_budget.test/api/v1/auth/login -H 'Content-Type: application/json' -d '{"email":"test@hr.mojo","password":"pass1234"}'` → `{success:true, data:{token:..., user:...}}`
- [ ] Same request with wrong password → 401 `{success:false, error:"Invalid credentials"}`
- [ ] `curl http://hr_budget.test/api/v1/auth/me -H "Authorization: Bearer <TOKEN>"` → user info
- [ ] Same request without header → 401
- [ ] `curl -X OPTIONS http://hr_budget.test/api/v1/auth/login -H 'Origin: http://localhost:5174' -v` → 204 with `Access-Control-Allow-Origin`
- [ ] Browser: login + refresh + logout + auto-redirect all work
- [ ] Legacy MVC: `http://hr_budget.test/login` (PHP view) still works unchanged

---

## Acceptance Criteria

- [ ] `composer.json` includes `firebase/php-jwt`
- [ ] `src/Api/`, `src/Services/`, `src/Dtos/`, `src/Core/Jwt.php` created
- [ ] `config/api.php` created with env-driven values
- [ ] `routes/web.php` registers `/api/v1/auth/login`, `/api/v1/auth/me`, `/api/v1/health`
- [ ] `public/index.php` applies CORS middleware for `/api/*` paths
- [ ] All unit + integration tests pass (`vendor/bin/phpunit`)
- [ ] `frontend/` directory exists with Vue 3 TS project
- [ ] `cd frontend && npm run typecheck && npm run build` succeed
- [ ] Browser flow: load `:5174` → login → dashboard (with user name) → logout → login again ✓
- [ ] Legacy MVC routes still render HTML as before (no regression)
- [ ] `.gitignore` updated for `frontend/node_modules`, `frontend/dist`
- [ ] `scripts/server-check.php` stays in repo (not in `public/` — it's a helper)

## Completion Checklist

- [ ] Code follows discovered patterns (namespace, static facade, DTO readonly, etc.)
- [ ] Error handling returns JSON envelope — no HTML/stack traces leaked
- [ ] No hardcoded secrets (JWT_SECRET from env)
- [ ] Tests follow `Tests\TestCase` + `@test` annotation style
- [ ] TypeScript strict mode enabled (no `any` in new code)
- [ ] Vue components use `<script setup lang="ts">`
- [ ] Thai copy in UI (labels, errors) — no EN-only strings
- [ ] No unnecessary scope additions (see NOT Building list)
- [ ] Self-contained — no questions needed to implement

## Risks

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Apache strips `Authorization` header on topzlab | Medium | High (auth breaks) | Document `.htaccess` fallback + `REDIRECT_HTTP_AUTHORIZATION` check in middleware |
| Existing `.htaccess` at root swallows `/api/*` requests | Medium | High (404s) | Test locally; update rewrite to exclude `/api/*` if needed (verify with curl) |
| User table missing `is_active` column | Low | Medium | Code gracefully handles missing key (Task 9 uses `array_key_exists`) |
| Tests requiring `JWT_SECRET` fail because phpunit.xml lacks env | Medium | Low | Add `<env name="JWT_SECRET" value="test-secret-min-32-chars-long-123"/>` to phpunit.xml |
| Tailwind 4 beta breaking changes | Low | Medium | Pin exact version in package.json; fallback to Tailwind 3 if incompatible |
| Vite proxy + CORS double-handling | Low | Low | In dev, proxy means same-origin → no CORS; prod will hit CORS middleware |
| Session + JWT coexist confusion | Medium | Medium | Clear docs: legacy uses session, new uses JWT, never both for same request |
| `createUser` helper doesn't exist in TestCase | Medium | Low | Verify before writing tests; add minimal seed helper if missing |

## Notes

- **Firebase PHP-JWT version**: targeting ^6.10 (latest stable as of 2026-04) — v7 is pre-release as of this writing.
- **Pinia version**: v3 (latest) — uses setup store composition syntax matching smart-port.
- **Why no DI container yet**: Manual `new AuthService()` is fine for single-service controller. If Day 2+ grows, introduce `php-di/php-di` then.
- **Why fetch not axios**: Zero deps, native, async/await. Smart-port uses axios — we diverge deliberately (lighter bundle).
- **Why separate `frontend/`**: matches smart-port, clean separation, doesn't disturb existing Vite build for legacy views.
- **Legacy Vite config unchanged**: `vite.config.js` (root) builds legacy JS for PHP views — keep as-is.
- **Subsequent days** will add: Budget Request CRUD (Day 2), Master data + file upload + notifications (Day 3), deploy + polish (Day 4).

---

*Source PRD: `.claude/PRPs/prds/hr-budget-rest-api-vue-refactor.prd.md`*
*Generated: 2026-04-17*

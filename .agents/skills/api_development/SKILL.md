---
name: api_development
description: Guide for building REST APIs in the HR Budget project. Includes routing, response formats, authentication, and error handling.
---

# API Development Assistant

Guide for creating and maintaining REST APIs in the HR Budget project.

## 📑 Table of Contents
- [Overview](#-overview)
- [API Routing](#-api-routing)
- [Response Format](#-response-format)
- [Authentication](#-authentication)
- [Error Handling](#-error-handling)
- [Common Patterns](#-common-patterns)
- [Testing APIs](#-testing-apis)

## 🌐 Overview

| Aspect | Detail |
|:-------|:-------|
| **Base Path** | `/api/` |
| **Format** | JSON |
| **Auth** | Session-based (same as web) |
| **CORS** | Not enabled (internal use) |

**Current Endpoints:**
- `/api/dashboard/chart-data` - Dashboard charts
- `/api/budget-plans/outputs` - Dropdown data
- `/api/budget-plans/activities` - Activity list

## 🛣️ API Routing

### Define Routes (`routes/web.php`)

```php
use App\Controllers\ApiController;

// GET endpoints
Router::get('/api/users', [ApiController::class, 'getUsers']);
Router::get('/api/users/{id}', [ApiController::class, 'getUser']);

// POST/PUT/DELETE
Router::post('/api/users', [ApiController::class, 'createUser']);
Router::put('/api/users/{id}', [ApiController::class, 'updateUser']);
Router::delete('/api/users/{id}', [ApiController::class, 'deleteUser']);
```

### Route Parameters
```php
// In Controller
public function getUser(int $id): void
{
    $user = User::find($id);
    $this->json($user);
}
```

## 📦 Response Format

### Standard JSON Response

```php
// Success
$this->jsonSuccess($data, 'Operation successful');

// Error
$this->jsonError('Validation failed', 422);
```

### Response Structure

**Success:**
```json
{
    "status": "success",
    "message": "Data retrieved",
    "data": { ... }
}
```

**Error:**
```json
{
    "status": "error",
    "message": "Invalid input",
    "errors": {
        "email": "Email is required"
    }
}
```

### Helper Methods (Add to `App\Core\Controller`)

```php
protected function json($data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

protected function jsonSuccess($data, string $message = 'Success'): void
{
    $this->json([
        'status' => 'success',
        'message' => $message,
        'data' => $data
    ]);
}

protected function jsonError(string $message, int $code = 400, array $errors = []): void
{
    $response = [
        'status' => 'error',
        'message' => $message
    ];
    if ($errors) {
        $response['errors'] = $errors;
    }
    $this->json($response, $code);
}
```

## 🔐 Authentication

### Require Login
```php
public function getUsers(): void
{
    Auth::require(); // Redirects if not logged in
    
    $users = User::getAll();
    $this->jsonSuccess($users);
}
```

### Check Permissions
```php
public function deleteUser(int $id): void
{
    Auth::requirePermission('users.delete');
    
    User::delete($id);
    $this->jsonSuccess(null, 'User deleted');
}
```

### API-Only Auth Check
```php
if (!Auth::check()) {
    $this->jsonError('Unauthorized', 401);
    return;
}
```

## ⚠️ Error Handling

### HTTP Status Codes
| Code | Usage |
|:-----|:------|
| `200` | Success |
| `201` | Created |
| `400` | Bad Request (validation) |
| `401` | Unauthorized |
| `403` | Forbidden |
| `404` | Not Found |
| `422` | Unprocessable Entity |
| `500` | Server Error |

### Validation Pattern
```php
public function createUser(): void
{
    $errors = [];
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Valid email is required';
    }
    
    if ($errors) {
        $this->jsonError('Validation failed', 422, $errors);
        return;
    }
    
    $id = User::create(['name' => $name, 'email' => $email]);
    $this->jsonSuccess(['id' => $id], 'User created');
}
```

## 🛠️ Common Patterns

### 1. List with Pagination
```php
public function getUsers(): void
{
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $offset = ($page - 1) * $limit;
    
    $users = User::paginate($limit, $offset);
    $total = User::count();
    
    $this->jsonSuccess([
        'items' => $users,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ]);
}
```

### 2. Dropdown Data
```php
public function getOptions(): void
{
    $options = FiscalYear::getAll();
    
    // Format for <select>
    $formatted = array_map(fn($fy) => [
        'value' => $fy['id'],
        'label' => $fy['name']
    ], $options);
    
    $this->jsonSuccess($formatted);
}
```

### 3. CSRF for POST/PUT/DELETE
```php
public function createItem(): void
{
    if (!View::verifyCsrf()) {
        $this->jsonError('Invalid CSRF token', 403);
        return;
    }
    
    // Process...
}
```

## 🧪 Testing APIs

### Using Playwright
```typescript
test('API returns user list', async ({ request }) => {
    const response = await request.get('/api/users');
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    expect(data.status).toBe('success');
    expect(Array.isArray(data.data)).toBeTruthy();
});
```

### Using cURL (Manual)
```bash
# GET
curl http://localhost/hr_budget/public/api/users

# POST with data
curl -X POST http://localhost/hr_budget/public/api/users \
  -d "name=Test&email=test@example.com"
```

### JavaScript Fetch
```javascript
fetch('/api/users', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ name: 'Test', email: 'test@moj.go.th' })
})
.then(res => res.json())
.then(data => console.log(data));
```

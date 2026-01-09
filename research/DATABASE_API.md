# Database API Documentation

## 🔐 Secure Database Inspection API

API สำหรับตรวจสอบและ Query ข้อมูล Database แบบ Read-Only

### 🔑 Token Authentication
```
Token: debug_2024_hr_budget_secure
```

### 📍 Endpoint
```
http://localhost/hr_budget/public/db-api.php
```

---

## 📋 Available Actions

### 1. List All Tables
```
GET /db-api.php?token=debug_2024_hr_budget_secure&action=tables
```

**Response:**
```json
{
  "success": true,
  "tables": ["budget_categories", "budget_requests", ...],
  "count": 12
}
```

---

### 2. Describe Table Structure
```
GET /db-api.php?token=debug_2024_hr_budget_secure&action=describe&table=budget_category_items
```

**Response:**
```json
{
  "success": true,
  "table": "budget_category_items",
  "columns": [...],
  "row_count": 45
}
```

---

### 3. Custom Query
```
GET /db-api.php?token=debug_2024_hr_budget_secure&action=query&query=SELECT * FROM budget_category_items LIMIT 5
```

**Security:**
- ✅ Only `SELECT`, `SHOW`, `DESCRIBE` allowed
- ❌ Blocks: `DROP`, `DELETE`, `UPDATE`, `INSERT`, `ALTER`, `TRUNCATE`, `CREATE`

---

### 4. Quick Preset Queries
```
GET /db-api.php?token=debug_2024_hr_budget_secure&action=quick&preset=category_items
```

**Available Presets:**
- `categories` - All budget categories
- `category_items` - Budget category items (first 20)
- `requests` - Recent 10 budget requests
- `request_items_5` - Items for Request #5
- `tables` - List all tables

---

## 🛡️ Security Features

1. **Localhost Only**: API only accessible from localhost
2. **Token Authentication**: Requires valid token
3. **Read-Only**: Only SELECT queries allowed
4. **Keyword Filtering**: Blocks dangerous SQL keywords
5. **JSON Output**: Structured responses

---

## 📝 Example Usage

### Check if Request #5 has items
```
http://localhost/hr_budget/public/db-api.php?token=debug_2024_hr_budget_secure&action=quick&preset=request_items_5
```

### Count category items
```
http://localhost/hr_budget/public/db-api.php?token=debug_2024_hr_budget_secure&action=query&query=SELECT COUNT(*) as total FROM budget_category_items
```

### View Personnel category items
```
http://localhost/hr_budget/public/db-api.php?token=debug_2024_hr_budget_secure&action=query&query=SELECT item_code, item_name, is_header, level FROM budget_category_items ORDER BY sort_order
```

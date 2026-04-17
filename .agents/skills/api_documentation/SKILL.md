---
name: api_documentation
description: Guide for documenting APIs using OpenAPI (Swagger) to ensure clear specifications and easy consumption.
---

# API Documentation Assistant (OpenAPI/Swagger)

Standardize API documentation for the HR Budget project.

## 📑 Table of Contents

- [OpenAPI Specification](#-openapi-specification)
- [Example `openapi.yaml`](#-example-openapiyaml)
- [Hosting Documentation](#-hosting-documentation)
- [Best Practices](#-best-practices)

## 📜 OpenAPI Specification

We use **OpenAPI 3.1** to describe our REST APIs.

### Key Benefits
- **Standardized**: Machine-readable format.
- **Interactive**: Allows testing via Swagger UI.
- **Code Generation**: Can generate client SDKs.

### File Location
Store specification files in `docs/api/`:
- `docs/api/openapi.yaml` (Main spec)
- `docs/api/components/` (Reusable schema definitions)

## 📄 Example `openapi.yaml`

```yaml
openapi: 3.1.0
info:
  title: HR Budget API
  version: 1.0.0
  description: API for managing HR Budget plans and requests.
  contact:
    name: HR Budget Team
    email: support@hrbudget.local
servers:
  - url: http://localhost:8000/api
    description: Local Development Server
  - url: https://hrbudget.example.com/api
    description: Production Server

paths:
  /budgets:
    get:
      summary: List all budgets
      description: Retrieve a list of budgets with optional fiscal year filter
      tags:
        - Budgets
      parameters:
        - in: query
          name: year
          schema:
            type: integer
            example: 2567
          description: Fiscal year filter (Thai Buddhist calendar)
      responses:
        '200':
          description: A list of budgets
          content:
            application/json:
              schema:
                type: object
                properties:
                  status:
                    type: string
                    example: success
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/Budget'
              examples:
                budgetList:
                  value:
                    status: success
                    data:
                      - id: 1
                        name: "งบบุคลากร"
                        amount: 5000000
                        year: 2567
                      - id: 2
                        name: "งบดำเนินงาน"
                        amount: 3000000
                        year: 2567
        '401':
          $ref: '#/components/responses/Unauthorized'
        '500':
          $ref: '#/components/responses/InternalError'
          
    post:
      summary: Create a new budget
      description: Create a new budget entry (requires authentication)
      tags:
        - Budgets
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/BudgetInput'
            examples:
              newBudget:
                value:
                  name: "งบลงทุน"
                  amount: 10000000
                  year: 2567
      responses:
        '201':
          description: Created successfully
          content:
            application/json:
              schema:
                type: object
                properties:
                  status:
                    type: string
                    example: success
                  data:
                    $ref: '#/components/schemas/Budget'
        '400':
          $ref: '#/components/responses/BadRequest'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '500':
          $ref: '#/components/responses/InternalError'

components:
  schemas:
    Budget:
      type: object
      properties:
        id:
          type: integer
          example: 1
        name:
          type: string
          example: "งบบุคลากร"
        amount:
          type: number
          format: double
          example: 5000000
        year:
          type: integer
          example: 2567
          
    BudgetInput:
      type: object
      required:
        - name
        - amount
      properties:
        name:
          type: string
          minLength: 3
          example: "งบลงทุน"
        amount:
          type: number
          format: double
          minimum: 0
          example: 10000000
        year:
          type: integer
          example: 2567
          
    Error:
      type: object
      properties:
        status:
          type: string
          example: error
        message:
          type: string
          example: "An error occurred"
        code:
          type: string
          example: "INVALID_REQUEST"

  responses:
    BadRequest:
      description: Bad Request - Invalid input
      content:
        application/json:
          schema:
            $ref: '#/components/schemas/Error'
          example:
            status: error
            message: "Validation failed"
            code: "VALIDATION_ERROR"
            
    Unauthorized:
      description: Unauthorized - User not logged in
      content:
        application/json:
          schema:
            $ref: '#/components/schemas/Error'
          example:
            status: error
            message: "Please login to continue"
            code: "UNAUTHORIZED"
            
    InternalError:
      description: Internal Server Error
      content:
        application/json:
          schema:
            $ref: '#/components/schemas/Error'
          example:
            status: error
            message: "Internal server error"
            code: "INTERNAL_ERROR"

  securitySchemes:
    sessionAuth:
      type: apiKey
      in: cookie
      name: PHPSESSID
      description: Session-based authentication using PHP sessions

security:
  - sessionAuth: []
```

## 🌐 Hosting Documentation

### 1. Swagger UI (Self-hosted)

You can serve Swagger UI via a static HTML file:

```html
<!-- public/docs.html -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>API Documentation</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui.css" />
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-bundle.js"></script>
    <script>
    window.onload = () => {
        window.ui = SwaggerUIBundle({
            url: '/docs/api/openapi.yaml',
            dom_id: '#swagger-ui',
        });
    };
    </script>
</body>
</html>
```

### 2. VS Code Extension

- **Recommendation**: Install `OpenAPI (Swagger) Editor` extension to preview files locally.

## ✅ Best Practices

1.  **Keep it Sync**: Update YAML whenever code changes.
2.  **Use Components**: Reuse Schema definitions ($ref) to avoid duplication.
3.  **Descriptive**: Add `summary`, `description`, and `example` to every field.
4.  **Security Definition**: Clearly define which endpoints require Auth.
5.  **Error Responses**: Always document error cases (400, 401, 403, 404, 500).
6.  **Request Examples**: Provide realistic examples for all request bodies.
7.  **Validation Rules**: Document constraints (minLength, minimum, pattern) in schemas.

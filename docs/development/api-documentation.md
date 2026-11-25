# API Documentation (OpenAPI / Swagger via Scramble)

> Interactive API documentation, OpenAPI 3.1 schema generation, and exploration tools for Lamsa.ma.

---

## 1. Overview & Access

Lamsa.ma provides interactive, real-time API documentation powered by **`dedoc/scramble`**. 

Unlike traditional Swagger setups that require cluttering controllers with verbose PHPDoc annotations, Scramble uses **AST (Abstract Syntax Tree) static analysis** to automatically infer:
- Route paths, HTTP methods, and URL parameters.
- Query parameters and request body schemas from Form Request `rules()` methods.
- Response payloads and status codes from JsonResource classes.

### Documentation Endpoints:
- **Interactive UI (Stoplight Elements):** `http://127.0.0.1:8000/docs/api`
- **Raw OpenAPI 3.1 JSON Specification:** `http://127.0.0.1:8000/docs/api.json`

---

## 2. Using the Interactive UI ("Try It Out")

The interactive explorer at `/docs/api` allows developers to test endpoints directly from the browser:

1. **Authentication**:
   - Open `/docs/api` in your browser.
   - Expand the **Authentication** section in the sidebar.
   - Run the `POST /api/v1/auth/login` endpoint with valid credentials to obtain a Bearer token.
   - Click the **Authorize** button at the top of the docs page and paste the token:
     ```text
     Bearer 1|abcdef123456...
     ```
   - All subsequent "Try it out" requests will automatically include the `Authorization` header.

2. **Inspecting Request Schemas**:
   - Click on any endpoint (e.g. `POST /api/v1/artisan/store`).
   - The UI displays field names, types, whether they are required/optional, and validation rules inferred directly from `UpdateStoreRequest.php`.

---

## 3. Configuration & Production Access Gate

Scramble's configuration lives in `config/scramble.php`:

### Restricting Access in Production
By default, Scramble is accessible in local development (`APP_ENV=local`). To prevent exposing public API schemas in production, access is protected by a Laravel Gate:

```php
// app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::define('viewApiDocs', function ($user = null) {
        // Only allow authenticated administrators to view API documentation in production
        return app()->environment('local') || ($user && $user->hasRole('admin'));
    });
}
```

---

## 4. Exporting the OpenAPI Specification

To export the OpenAPI JSON file for external API gateways, Postman, or frontend code generation:

```bash
# Fetch raw OpenAPI 3.1 JSON
curl http://127.0.0.1:8000/docs/api.json -o openapi.json
```


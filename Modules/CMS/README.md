# CMS Module Documentation

Welcome to the **CMS (Content Management System) Module** for **Lamsa.ma**. This module provides a headless, API-driven solution for managing and serving editorial, static, and legal content (e.g. Terms of Service, Privacy Policy, About Us, FAQ, and Artisan Guidelines).

---

## 1. Architectural Strategy

The CMS module follows an **API-first / Headless architecture**, decoupling content storage and administration from frontend presentation layers (web, mobile, SSR):

- **Dual-Controller Architecture:** 
  - Administrative operations (`CRUD`) reside in [`Http/Controllers/Admin/PageController`](app/Http/Controllers/Admin/PageController.php).
  - Public read-only endpoints reside in [`Http/Controllers/PageController`](app/Http/Controllers/PageController.php).
- **Separation of API Resources:**
  - Admin endpoints return full model telemetry via [`Admin\PageResource`](app/Http/Resources/Admin/PageResource.php), exposing internal database IDs, timestamps, and draft states.
  - Public endpoints use specialized resources ([`PageListResource`](app/Http/Resources/PageListResource.php) and [`PageDetailResource`](app/Http/Resources/PageDetailResource.php)) that enforce data minimization and filter out non-essential attributes.
- **Performance & Column Projection:**
  - When listing public pages for navigation menus or footers, the module queries only `['id', 'title', 'slug']`. This avoids loading the heavy `content` column (`longtext` stored in MySQL overflow pages) into memory.

---

## 2. Key Concepts & Security Invariants

### A. Information Disclosure Prevention (Drafts vs 404)
When an unauthenticated user or client requests an unpublished draft via `/api/v1/pages/{slug}`, the system returns an **HTTP `404 Not Found`**, not `403 Forbidden`:
- Query: `Page::query()->published()->where('slug', $slug)->firstOrFail()`
- **Why:** Returning `403 Forbidden` would confirm to attackers or competitors that an unreleased draft or secret page exists. A `404` makes unpublished pages indistinguishable from non-existent URLs.

### B. Slug Generation & Collision Resolution
Pages use human-readable, URL-safe slugs for clean routing:
- If an admin submits a page without a custom slug, the system automatically creates one from the title using `Str::slug($title)`.
- If a slug collision occurs (e.g. two pages titled "About Us"), [`PageController::generateUniqueSlug()`](app/Http/Controllers/Admin/PageController.php) automatically appends an incrementing numerical suffix (`about-us-1`, `about-us-2`).

### C. Data Minimization (Public Payloads)
Public endpoints deliberately exclude:
- `id`: Prevents internal database auto-increment enumeration.
- `is_published`: Redundant to external consumers (all reachable public pages are published).
- `created_at`: Irrelevant for visitors; only `updated_at` is shared for editorial revision reference.

### D. Role-Based Access Control (RBAC)
Administrative CMS routes are protected by two layers:
1. Route middleware: `auth:sanctum` and `role:admin`.
2. Form Request authorization: [`StorePageRequest`](app/Http/Requests/Admin/StorePageRequest.php) and [`UpdatePageRequest`](app/Http/Requests/Admin/UpdatePageRequest.php) verify `$this->user()?->isAdmin()`.

---

## 3. Database Schema

The `pages` table is defined in `database/migrations/2024_01_01_000013_create_pages_table.php`:

| Column | Type | Modifiers | Description |
| :--- | :--- | :--- | :--- |
| `id` | `unsignedBigInteger` | Auto-increment, Primary | Unique record identifier. |
| `title` | `string` | | The title of the page (e.g. "Terms of Service"). |
| `slug` | `string` | Unique, Indexed | URL-friendly slug used for public lookup. |
| `content` | `longText` | | Rich text, HTML, or Markdown body content. |
| `is_published` | `boolean` | Default: `true` | Publication status flag. |
| `created_at` | `timestamp` | Nullable | Record creation timestamp. |
| `updated_at` | `timestamp` | Nullable | Last modification timestamp. |

---

## 4. Feature & Route Map

All routes are registered inside [`routes/api.php`](routes/api.php) and prefixed with `api/v1` by [`CMSModuleServiceProvider`](app/Providers/CMSModuleServiceProvider.php).

### Public Endpoints (No Authentication Required)

| Method | Endpoint | Resource | Purpose / Mechanics |
| :--- | :--- | :--- | :--- |
| `GET` | `/pages` | `PageListResource` | Returns published page titles and slugs for website footers and menus. Projected to avoid loading `content`. |
| `GET` | `/pages/{slug}` | `PageDetailResource` | Returns title, slug, content, and `updated_at` for a published page. Returns `404` if unpublished or missing. |

### Admin Endpoints (`auth:sanctum`, `role:admin`)

| Method | Endpoint | Form Request | Purpose / Mechanics |
| :--- | :--- | :--- | :--- |
| `GET` | `/admin/pages` | None | Paginated list of all pages (both drafts and published) with pagination metadata. |
| `POST` | `/admin/pages` | `StorePageRequest` | Creates a new page. Automatically generates and disambiguates the slug if omitted. |
| `GET` | `/admin/pages/{page}` | None | Fetches full page telemetry by ID via Route Model Binding. |
| `PATCH` | `/admin/pages/{page}` | `UpdatePageRequest` | Updates page title, content, slug, or publication status (`is_published`). |
| `DELETE` | `/admin/pages/{page}` | None | Permanently removes the page record from the database. |

---

## 5. Automated Testing & Verification

The CMS module includes feature tests covering full CRUD lifecycles, guest isolation, authorization, and data minimization:

```bash
# Run all CMS feature tests
php artisan test Modules/CMS/tests/Feature/AdminPageCrudTest.php
php artisan test Modules/CMS/tests/Feature/PublicPageTest.php
```


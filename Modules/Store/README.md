# Store Module (`Modules/Store`)

The **Store Module** manages artisan storefront profiles, unique merchant slug generation, public storefront browsing, and store branding assets (logos).

---

## 1. Domain-Driven Architecture

```
Modules/Store/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ArtisanStoreApiController.php   # GET /artisan/store, POST, PUT, DELETE logo
│   │   │   └── PublicStoreApiController.php    # GET /stores/{slug}
│   │   ├── Requests/
│   │   │   ├── StoreStoreRequest.php
│   │   │   └── UpdateStoreRequest.php
│   │   └── Resources/
│   │       └── StoreResource.php               # Formats logo_url, artisan bio, product counts
│   ├── Models/
│   │   └── Store.php                           # Eloquent model with active scope and relations
│   ├── Policies/
│   │   └── StorePolicy.php                     # Ownership & one-store-per-artisan rule
│   ├── Providers/
│   │   └── StoreModuleServiceProvider.php      # Registers /api/v1 routes and policies
│   └── Services/
│       └── StoreService.php                    # Slug collision resolution, file disk cleanup
├── database/
│   └── factories/
│       └── StoreFactory.php
├── routes/
│   └── api.php
└── tests/
    └── Feature/
        ├── ArtisanStoreApiTest.php             # 9 feature tests for artisan management
        └── PublicStoreApiTest.php              # 3 feature tests for public catalog browsing
```

---

## 2. API Endpoints Map (`/api/v1`)

### Public Endpoints
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/v1/stores/{slug}` | View public artisan store by slug with active products count |

### Artisan Endpoints (`auth:sanctum`, `role:artisan`)
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/v1/artisan/store` | Fetch authenticated artisan's store profile |
| `POST` | `/api/v1/artisan/store` | Create store profile with name, description, and logo |
| `PUT / POST` | `/api/v1/artisan/store/update` | Update store profile details or replace logo |
| `DELETE` | `/api/v1/artisan/store/logo` | Remove existing store logo from public storage disk |

---

## 3. Key Design Patterns & Decisions

1. **Slug Collision Resolution:** Handled in `StoreService::generateUniqueSlug()`. If another artisan registers a store with the same name, incrementing numerical suffixes are automatically assigned (`slug`, `slug-1`, `slug-2`).
2. **File Asset Cleanup:** When an artisan updates their logo or deletes it, `StoreService` deletes the old file from `Storage::disk('public')` to prevent orphaned disk bloat.
3. **Multipart-PUT Compatibility:** Because standard PHP does not natively parse multipart file uploads on `PUT` requests, `/artisan/store/update` accepts `POST` (with or without `_method=PUT`) alongside `PUT`/`PATCH`.


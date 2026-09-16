# Product Module

The Product Module is responsible for handling the main catalog logic in Lamsa. It ensures that buyers can find products quickly using search, category/price filters, and sorting, while strictly preventing unpublished, soft-deleted, or inactive products from being publicly visible.

## Architecture & Encapsulation
- **Gateway Service Pattern:** All queries to fetch catalog products pass through `ProductCatalogService`. Controllers don't query the model directly to apply filters; they delegate to the service layer. This ensures visibility rules (`scopeVisible()`) are always applied consistently.
- **Model Scopes:** The `Product` model includes query scopes for complex operations like `scopeSearch`, `scopeInCategory`, and `scopeInPriceRange`, keeping the query logic encapsulated and reusable.
- **Computed Attributes / N+1 Prevention:** To avoid loading thousands of reviews per product or encountering N+1 traps, the module retrieves average ratings via SQL aggregates (`withAvg('reviews', 'rating')`), completely removing the `getAverageRatingAttribute()` accessor.

## Key Concepts

- **Visibility Indexing:** The `products` table has a composite index on `(is_published, status, deleted_at)`. Since the catalog heavily filters on these three attributes, the database uses this composite index to find active/published products instantly.
- **Categories:** The module queries `Category` models, but ensures they're ordered alphabetically and filters out inactive categories or products.
- **Module Migrations:** All schema definitions (`categories`, `products`, `reviews`, and index modifications) live within `Modules/Product/database/migrations/` and are registered via `ProductModuleServiceProvider`.

## API Endpoints

### 1. Catalog Search and List (`GET /api/v1/products`)
List products with filtering, search, and sort functionality.

**Parameters:**
- `q` (string): Search query (matches name and description).
- `category` (string): Slug of the category.
- `min_price` (numeric): Minimum price filter.
- `max_price` (numeric): Maximum price filter.
- `sort` (enum): `newest`, `price_low`, `price_high`, `rating`. Defaults to `newest`.
- `per_page` (integer): Max 50 items per page.

### 2. Product Detail (`GET /api/v1/products/{product:slug}`)
Retrieve a single product along with its category, store, artisan owner, and aggregated review summary.

### 3. Categories List (`GET /api/v1/categories`)
Retrieve a list of active categories along with the count of *visible* products inside each category.

# ADR 0003: Event-Driven Cache Invalidation over Cache Tags

- **Status:** Accepted
- **Date:** 2026-11-08
- **Deciders:** Engineering Architecture Team
- **Technical Context:** Strategic caching for CMS pages and Product categories with automated invalidation

---

## Context and Problem Statement

To minimize database load, frequently read and rarely changed resources (like the category tree and CMS editorial pages) are cached in memory for 24 hours. However, when an administrator or artisan modifies a category name, updates a page slug, or publishes a new product, cached copies become stale.

We needed a strategy to invalidate the cache instantly upon database changes.

---

## Considered Options

1. **Option 1: Cache Tags (`Cache::tags(['categories'])->flush()`)**
   - *How it works:* Tag cached items and flush the entire tag group upon mutation.
   - *Pros:* Grouped invalidation without tracking individual keys.
   - *Cons:* **Driver Incompatibility**: Cache tags are only supported by Redis and Memcached. When running local development or environments using the `database` or `file` cache drivers, calling `Cache::tags()` throws an unsupported driver `BadMethodCallException`.
2. **Option 2: Time-To-Live (TTL) Passive Expiration Only**
   - *How it works:* Set a short TTL (e.g. 5 minutes) and let items expire naturally.
   - *Pros:* Zero invalidation code.
   - *Cons:* Stale data window: Users and admins see outdated prices, categories, or content for up to 5 minutes after saving.
3. **Option 3: Event-Driven Model Observers with Explicit Keys**
   - *How it works:* Use Eloquent Observers (`CategoryObserver`, `PageObserver`, `ProductObserver`) listening to `saved` and `deleted` model events, explicitly calling `Cache::forget($key)`.
   - *Pros:*
     - 100% portable across all cache drivers (`redis`, `database`, `file`, `array`).
     - Automatic cross-entity invalidation (e.g. `ProductObserver` invalidates `active_categories_list` when a product is published or unpublished).
     - Centralized in model lifecycle: updates triggered via API, seeders, or Artisan CLI commands all trigger invalidation reliably.

---

## Decision Outcome

**Chosen Option:** Option 3 — **Event-Driven Observers with Explicit Key Eviction**.

### Implemented Architecture
- `Modules\CMS\Observers\PageObserver`: Purges `'cms_pages_list'` and `'cms_page_' . $page->slug`. Also inspects `$page->wasChanged('slug')` to purge both old and new slug cache keys.
- `Modules\Product\Observers\CategoryObserver`: Purges `'active_categories_list'`.
- `Modules\Product\Observers\ProductObserver`: Purges `'active_categories_list'` to ensure category product counts are never stale.
- Accompanied by HTTP response headers: `Cache-Control: public, max-age=3600, stale-while-revalidate=60` to enable edge CDN caching with automated background revalidation.


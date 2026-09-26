# Order Module Documentation

Welcome to the **Order Module** for **Lamsa.ma**. This document serves as the comprehensive architectural and technical reference for developers maintaining or extending the purchasing, shopping cart, checkout, and order fulfillment systems.

---

## 1. Domain Overview & Bounded Context

In Domain-Driven Design (DDD), the purchasing workflow is divided into three distinct lifecycle phases:

```
┌─────────────────────────┐       ┌─────────────────────────┐       ┌─────────────────────────┐
│          CART           │ ───►  │        CHECKOUT         │ ───►  │          ORDER          │
│   (Mutable Session)     │       │   (Atomic Transition)   │       │   (Immutable Contract)  │
└─────────────────────────┘       └─────────────────────────┘       └─────────────────────────┘
```

1. **Cart (Pre-Purchase / Mutable):** Temporary buyer basket. Items can be added, modified, removed, or merged from client-side guest storage.
2. **Checkout (Transition / Atomic):** Transitioning buyer intent into legal vendor contracts. Performs concurrency row locking, verifies live availability and prices, splits items into per-artisan sub-orders, decrements inventory, snapshots product data, and flushes the basket.
3. **Order & OrderItem (Post-Purchase / Immutable):** Permanent financial and fulfillment records. Tracks multi-stage delivery state machines for buyers and artisans.

---

## 2. Database Architecture & Schemas

The module defines 4 core tables under `Modules/Order/database/migrations/`:

### A. `carts` & `cart_items`
* `carts`
  * `id`: Primary Key.
  * `user_id`: Foreign Key (`users.id`, `cascadeOnDelete`, indexed). Each registered buyer has exactly one cart record (`firstOrCreate`).
* `cart_items`
  * `id`: Primary Key.
  * `cart_id`: Foreign Key (`carts.id`, `cascadeOnDelete`, indexed).
  * `product_id`: Foreign Key (`products.id`, `cascadeOnDelete`, indexed).
  * `quantity`: Unsigned integer (`min: 1`).
  * `unique(['cart_id', 'product_id'])`: Prevents duplicate rows for the same product; quantities are incremented instead.

### B. `orders` & `order_items`
* `orders`
  * `id`: Primary Key.
  * `user_id`: Foreign Key (`users.id`, `cascadeOnDelete`, indexed) — the buyer.
  * `artisan_id`: Foreign Key (`artisans.id`, `cascadeOnDelete`, indexed) — the vendor fulfilling this sub-order.
  * `status`: String enum (`pending`, `processing`, `shipped`, `delivered`, `cancelled`).
  * `total_amount`: Decimal (10, 2) — sum of `(quantity * unit_price)` for this specific artisan's items.
  * `shipping_address`: Text.
  * `payment_method`: String (`cash`, `card`).
  * `payment_status`: String (`unpaid`, `paid`, `cash_on_delivery`).
  * `notes`: Nullable string.
* `order_items`
  * `id`: Primary Key.
  * `order_id`: Foreign Key (`orders.id`, `cascadeOnDelete`, indexed).
  * `product_id`: Foreign Key (`products.id`, `restrictOnDelete`, indexed).
  * `artisan_id`: Foreign Key (`artisans.id`, `restrictOnDelete`, indexed).
  * `product_name`: **Snapshot string**. Captures the product name at checkout time.
  * `quantity`: Unsigned integer.
  * `unit_price`: **Snapshot decimal (10, 2)**. Captures unit price at purchase time.

---

## 3. Core Services & Underlying Mechanics

### A. `CartService` ([Modules/Order/app/Services/CartService.php](file:///c:/laragon/www/Lamsa.ma/Modules/Order/app/Services/CartService.php))
- **Stateless REST Architecture:** Eliminates PHP server-side cookie sessions (`session('cart')`). Mobile and web frontends store unauthenticated items locally.
- **Guest-to-User Merging (`sync`):** On login, the client submits guest items via `POST /api/v1/cart/sync`. The service merges them with any items already present in the user's database cart, automatically clamping quantities so `existing + guest <= live_stock`.
- **Eager Loading without Restrictive Scopes:** Carts load products along with `category` and `store` without filtering out inactive or soft-deleted products. This allows [CartItemResource](file:///c:/laragon/www/Lamsa.ma/Modules/Order/app/Http/Resources/CartItemResource.php) to calculate `is_available: false` and render the product title, rather than rendering a broken, missing record.

### B. `OrderCheckoutService` ([Modules/Order/app/Services/OrderCheckoutService.php](file:///c:/laragon/www/Lamsa.ma/Modules/Order/app/Services/OrderCheckoutService.php))
- **Two-Level Row-Level Locking (`lockForUpdate`):**
  1. Acquires an exclusive row lock on the user's `carts` record (`SELECT ... FOR UPDATE`).
  2. Acquires exclusive row locks on the purchased `products` records.
- **Race Condition Immunity:** If a user submits duplicate checkout requests simultaneously (e.g. double-tapping "Place Order" on high latency), the second request is queued by MySQL engine locks until the first transaction commits. When the second transaction runs, it finds the cart already empty and safely aborts.
- **Multi-Vendor Order Splitting:** Items are grouped by `artisan_id`. A multi-artisan checkout produces separate, decoupled `Order` records, allowing each artisan to manage their own shipments independently.
- **Authoritative Pricing:** Ignores any client-supplied price or stale cart value. Totals and snapshots are calculated strictly from live database prices at the millisecond of checkout.

### C. `OrderService` ([Modules/Order/app/Services/OrderService.php](file:///c:/laragon/www/Lamsa.ma/Modules/Order/app/Services/OrderService.php))
- **Order State Machine:** Enforces valid status transitions:
  * `Pending` $\rightarrow$ `Processing` or `Cancelled`
  * `Processing` $\rightarrow$ `Shipped` or `Cancelled`
  * `Shipped` $\rightarrow$ `Delivered`
- **Soft-Delete Stock Restoration:** When a buyer cancels a pending order, the service restores stock (`$item->product?->increment('stock', $item->quantity)`). Because `OrderItem::product()` uses `withTrashed()`, stock is restored even if the artisan soft-deleted the product after the order was placed.

---

## 4. Order Lifecycle & Authorization Matrix

| User Role | Action | Allowed Statuses | Policy Gate |
| :--- | :--- | :--- | :--- |
| **Buyer** | View their orders | Any | [OrderPolicy@view](file:///c:/laragon/www/Lamsa.ma/Modules/Order/app/Policies/OrderPolicy.php) (`$order->user_id === $user->id`) |
| **Buyer** | Cancel order | `pending` only | [OrderPolicy@cancel](file:///c:/laragon/www/Lamsa.ma/Modules/Order/app/Policies/OrderPolicy.php) (restores inventory) |
| **Artisan** | View received orders | Any | [OrderPolicy@view](file:///c:/laragon/www/Lamsa.ma/Modules/Order/app/Policies/OrderPolicy.php) (`$order->artisan_id === $user->artisan->id`) |
| **Artisan** | Update status | `processing` $\rightarrow$ `shipped` $\rightarrow$ `delivered` | [OrderPolicy@updateStatus](file:///c:/laragon/www/Lamsa.ma/Modules/Order/app/Policies/OrderPolicy.php) |
| **Admin** | Full management | Any status | Bypasses ownership constraints |

---

## 5. REST API Route Map

All routes are prefixed with `/api/v1` and protected under `auth:sanctum`.

### A. Shopping Cart (`/api/v1/cart`)

| Method | Endpoint | Form Request | Purpose |
| :--- | :--- | :--- | :--- |
| `GET` | `/cart` | None | Fetch current user's cart, items, live availability, and totals. |
| `POST` | `/cart/items/{product}` | `AddCartItemRequest` | Add item to cart (`quantity`). Validates active status and stock. |
| `PATCH` | `/cart/items/{product}` | `UpdateCartItemRequest`| Update existing item quantity (`quantity`). |
| `DELETE`| `/cart/items/{product}` | None | Remove a specific product from the cart. |
| `DELETE`| `/cart` | None | Clear all items from the cart. |
| `POST` | `/cart/sync` | `SyncCartRequest` | Merge client-side guest cart items on login (`items: [{ product_id, quantity }]`). |

### B. Checkout (`/api/v1/checkout`)

| Method | Endpoint | Middleware | Form Request | Purpose |
| :--- | :--- | :--- | :--- | :--- |
| `POST` | `/checkout` | `throttle:checkout` | `CheckoutRequest` | Atomic checkout. Validates stock, locks rows, decrements inventory, splits orders by artisan, snapshots products, and clears cart. |

### C. Buyer Orders (`/api/v1/buyer/orders`)

| Method | Endpoint | Policy Check | Purpose |
| :--- | :--- | :--- | :--- |
| `GET` | `/buyer/orders` | `viewAny` | Paginated listing of orders placed by authenticated user (`?per_page=10`). |
| `GET` | `/buyer/orders/{order}` | `view` | View order details, items, artisan store info, and shipment status. |
| `POST` | `/buyer/orders/{order}/cancel` | `cancel` | Cancel pending order. Automatically restores stock for all items. |

### D. Artisan Orders (`/api/v1/artisan/orders`)

| Method | Endpoint | Form Request | Policy Check | Purpose |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/artisan/orders` | None | `artisan` role | Paginated listing of incoming orders for the artisan's store. |
| `GET` | `/artisan/orders/{order}` | None | `view` | View order items and buyer delivery details. |
| `PATCH`| `/artisan/orders/{order}/status` | `UpdateOrderStatusRequest` | `updateStatus` | Advance order status (`status: shipped` or `delivered`). |

---

## 6. Edge Cases & Invariants Fortified

1. **Concurrent Double-Checkout Immunity:** Row-level locking on `Cart` serializes checkout attempts from the same buyer; duplicate requests safely return HTTP 422 (`"Your cart is empty."`).
2. **Soft-Deleted Product Resilience:** `OrderItem::product()` and `CartItem::product()` utilize `->withTrashed()`. Soft-deleting an item after purchase does not corrupt invoices or fail order cancellation stock restores.
3. **Stale/Unavailable Cart Detection:** [CartResource](file:///c:/laragon/www/Lamsa.ma/Modules/Order/app/Http/Resources/CartResource.php) flags `has_unavailable_items: true` and isolates `is_available: false` items, excluding them from `total_price` so users can resolve inventory issues before checkout.
4. **Authoritative Price Synchronization:** Checkout calculates totals from fresh database records, preventing exploits where clients try to check out using older, cheaper prices.
5. **Guest Cart Overflow Capping:** Merging guest items into pre-existing cart items dynamically clamps quantities to the maximum available stock.

---

## 7. Quality Assurance & Testing

The module maintains 100% test coverage for all features, policies, and edge cases.

### Run Module Tests
```bash
php artisan test Modules/Order
```

### Run Full Application Suite
```bash
php artisan test
```

### Static Analysis (Larastan Level 5+)
```bash
./vendor/bin/phpstan analyse --memory-limit=2G
```

### Code Style (Laravel Pint)
```bash
php vendor/bin/pint --test
```

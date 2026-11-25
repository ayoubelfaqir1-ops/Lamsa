# Testing Strategy & Execution

> Testing philosophy, test suite architecture, SQLite vs MySQL environments, and guidelines for writing robust tests.

---

## 1. Testing Philosophy (The Testing Trophy)

Lamsa.ma balances fast internal tests with automated API-level E2E tests:

```
                ┌───────────────────────────────────┐
                │   End-to-End API / Fuzzing        │  <-- Playwright API & Schemathesis
                │  (Real network, real DB, no mocks)│
                ├───────────────────────────────────┤
                │     Subcutaneous Feature Tests    │  <-- 90% of Lamsa suite (In-process)
                ├───────────────────────────────────┤
                │             Unit Tests            │  <-- Pure calculations, isolated algorithms
                └───────────────────────────────────┘
```

**Concept: Subcutaneous Feature Testing**
> Rather than testing a controller or service in isolation with mocked dependencies, a feature test sends an actual HTTP JSON request into the kernel. It executes the full route, middleware, Form Request validation, database transactions, model observers, and JSON resource transformations.

---

## 2. Test Execution Environments: SQLite vs MySQL

To balance **development speed** with **production fidelity**, Lamsa uses a two-tier database strategy for testing:

| Environment | Database Engine | Speed | Purpose |
| :--- | :--- | :--- | :--- |
| **Local Development** | SQLite `:memory:` | ~30s for 200+ tests | Instant feedback during coding without touching local MySQL databases. |
| **Pull Request CI** | SQLite `:memory:` | ~45s in GitHub Actions | Fast PR approval gates without waiting for Docker containers. |
| **Deploy Gate (`main`)**| MySQL 8.0 Container | ~1m 30s | Final pre-production gatekeeper verifying 100% MySQL InnoDB compatibility. |

---

## 3. Running Feature & Unit Tests (PHPUnit)

### Run the Full Suite
```bash
php artisan test
```

### Run Tests for a Specific Domain Module
```bash
# Test the Auction module
php artisan test Modules/Auction/tests

# Test the Store module feature tests
php artisan test Modules/Store/tests/Feature
```

### Run a Specific Test Class
```bash
php artisan test Modules/Order/tests/Feature/CheckoutApiTest.php
```

### Filter Tests by Name
```bash
php artisan test --filter=test_buyer_can_place_valid_bid
```

---

## 4. Writing Robust Feature Tests (Standard Patterns)

All feature tests extend `Tests\TestCase` and use the `Illuminate\Foundation\Testing\RefreshDatabase` trait.

### Pattern 1: Authentication & Role Verification
Always test both the authorized path and the forbidden/unauthenticated paths:
```php
public function test_guest_cannot_create_store(): void
{
    $response = $this->postJson('/api/v1/artisan/store', ['name' => 'My Store']);
    $response->assertStatus(401);
}

public function test_artisan_can_create_store(): void
{
    $artisanUser = User::factory()->artisan()->create();

    $response = $this->actingAs($artisanUser)
        ->postJson('/api/v1/artisan/store', [
            'name' => 'Atlas Weaving',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Atlas Weaving');

    $this->assertDatabaseHas('stores', ['name' => 'Atlas Weaving']);
}
```

### Pattern 2: Side-Effect & Filesystem Verification
When testing file uploads (e.g. store logos or product images), always use `Storage::fake()`:
```php
public function test_artisan_can_upload_logo(): void
{
    Storage::fake('public');

    $file = UploadedFile::fake()->image('logo.jpg', 400, 400);

    $this->actingAs($artisan)
        ->postJson('/api/v1/artisan/store', [
            'name' => 'Clay Works',
            'logo' => $file,
        ]);

    Storage::disk('public')->assertExists('stores/logos/' . $file->hashName());
}
```

### Pattern 3: Event Broadcasting Verification
When testing WebSocket broadcasts (e.g. real-time auction bids), use `Event::fake()`:
```php
public function test_bid_dispatches_broadcast_event(): void
{
    Event::fake([BidPlaced::class]);

    $this->actingAs($buyer)->postJson("/api/v1/auctions/{$auction->id}/bids", [
        'amount' => 500,
    ]);

    Event::assertDispatched(BidPlaced::class, function ($event) use ($auction) {
        return $event->bid->auction_id === $auction->id && $event->bid->amount == 500;
    });
}
```

---

## 5. Automated API-Level E2E & Contract Fuzzing

In addition to in-process feature tests, Lamsa includes an **automated API-level E2E suite** that runs over the wire against a live running server (`http://127.0.0.1:8000`).

### Prerequisites for E2E Tests
Because E2E tests run against your **real running MySQL database**, ensure the database has been seeded with demo users and products:
```bash
php artisan migrate:fresh --seed
```

### A. Run Multi-Step Business Journeys (Playwright API Mode)
Tests the 3 critical business paths over the wire:
- **Journey 1 (`01-buyer-commerce-checkout.spec.ts`)**: Browse catalog ➔ dynamically select in-stock product ➔ add to cart ➔ atomic checkout with multi-vendor sub-order splitting ➔ assert presence in order history.
- **Journey 2 (`02-artisan-auction-bidding.spec.ts`)**: Discover active auctions ➔ calculate higher bid ➔ submit bid via API ➔ assert updated public price ➔ assert private bid history.
- **Journey 3 (`03-wishlist-and-reviews.spec.ts`)**: Bookmark product ➔ verify presence in `/api/v1/favorites` ➔ inspect reviews ➔ cleanup bookmark.

```bash
# 1. Start your local server in Terminal 1
php artisan serve

# 2. In Terminal 2, run the automated E2E journeys
npm run test:e2e
```

### B. Run Automated Zero-Code Contract Fuzzing (Schemathesis)
Fuzzes all 70 operations automatically using the Scramble OpenAPI specification:
```bash
# Automatically tests all endpoints for 500 errors, schema compliance, and boundary edge cases
npm run test:fuzz
```

---

## 6. Traceability, Reports & Root-Cause Diagnostics

When an E2E test fails, Lamsa provides multiple layers of traceability to instantly diagnose the exact failure:

### 1. Interactive HTML Report
To view the visual test dashboard showing duration, steps, and status of each test:
```bash
npm run test:e2e:report
```
*Configured with `open: 'on-failure'` in `playwright.config.ts`, so the dashboard automatically pops up in your browser whenever a test fails.*

### 2. Playwright Trace Viewer (`trace: 'retain-on-failure'`)
Playwright records a complete HAR network archive and execution trace on failure. You can inspect the exact URL, request headers, JSON payloads, and response bodies like in Chrome DevTools:
```bash
npx playwright show-trace test-results/<failed-test-folder>/trace.zip
```

### 3. Server-Side Exception Logs
If an API request returns an unexpected `500 Server Error`, inspect the full PHP stack trace and SQL query in:
```bash
tail -n 50 storage/logs/laravel.log
```

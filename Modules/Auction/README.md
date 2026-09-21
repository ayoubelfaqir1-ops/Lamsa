# Auction Module (`Modules/Auction`)

The **Auction Module** manages time-window competitive bidding, artisan auction setup, real-time WebSocket event broadcasting (via Laravel Reverb), high-concurrency pessimistic locking, and automated cron sweepers for unique handcrafted artisan items on the Lamsa platform.

---

## 1. Domain-Driven Architecture

```
Modules/Auction/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── CloseExpiredAuctionsCommand.php  # Scheduled sweeper: auctions:close-expired
│   ├── Enums/
│   │   └── AuctionStatus.php                    # scheduled, active, ended, cancelled
│   ├── Events/
│   │   ├── BidPlaced.php                        # Broadcasts new bids to private-auction.{id}
│   │   └── AuctionClosed.php                    # Broadcasts winner outcome to private-auction.{id}
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PublicAuctionApiController.php   # Catalog browsing & auction details
│   │   │   ├── BuyerBidApiController.php        # Placing & viewing bids
│   │   │   └── ArtisanAuctionApiController.php  # Artisan auction setup, metrics, cancellation
│   │   ├── Requests/
│   │   │   ├── StoreAuctionRequest.php
│   │   │   ├── UpdateAuctionRequest.php
│   │   │   └── PlaceBidRequest.php
│   │   └── Resources/
│   │       ├── AuctionResource.php              # Exposes public data; shields reserve_price
│   │       └── BidResource.php                  # Bidder details and amount
│   ├── Models/
│   │   ├── Auction.php
│   │   └── Bid.php
│   ├── Policies/
│   │   └── AuctionPolicy.php                    # Role and ownership authorization
│   ├── Providers/
│   │   └── AuctionModuleServiceProvider.php     # Rate limiting, policies, routes & channels
│   └── Services/
│       ├── AuctionService.php                   # CRUD, metrics, idempotent closeExpired()
│       └── BidService.php                       # lockForUpdate() bidding & concurrency
├── database/
│   ├── factories/
│   │   ├── AuctionFactory.php
│   │   └── BidFactory.php
│   └── migrations/
│       ├── 2024_01_01_000006_create_auctions_table.php
│       └── 2024_01_01_000007_create_bids_table.php
├── routes/
│   ├── api.php                                  # REST endpoints (/api/v1/auctions/...)
│   └── channels.php                             # Broadcast channel auth: auction.{id}
└── tests/
    └── Feature/
        ├── ArtisanAuctionApiTest.php
        ├── BuyerBidApiTest.php
        ├── PublicAuctionApiTest.php
        ├── CloseExpiredAuctionsTest.php
        └── AuctionBroadcastingTest.php          # Reverb channel authorization tests
```

---

## 2. Full End-to-End Auction Lifecycle

```
[1. Creation]
Artisan creates auction (POST /api/v1/artisan/auctions)
  └─ Sets starting_price, reserve_price (hidden), starts_at, ends_at.
  └─ Status: `scheduled`

[2. Activation]
When now() >= starts_at:
  └─ Status transitions to `active`. Bidding opens to authenticated buyers.

[3. Live Bidding & Real-Time Broadcasting]
Buyer places bid (POST /api/v1/auctions/{id}/bids)
  ├─ BidService locks target auction row with `lockForUpdate()` in DB transaction.
  ├─ Validates bid > current_price and auction is active.
  ├─ Updates `current_price` and persists `Bid` record.
  └─ Dispatches `BidPlaced` event (implements ShouldBroadcastNow).
       └─ Laravel API pushes payload to Laravel Reverb (:8080).
       └─ Reverb fans out WebSocket frame to all connected clients on `private-auction.{id}`.

[4. Expiration Sweeper & Resolution]
Scheduler runs `php artisan auctions:close-expired` every minute:
  ├─ Finds active auctions where now() >= ends_at.
  ├─ Evaluates highest bid against `reserve_price`:
  │    ├─ If highest bid >= reserve_price:
  │    │    └─ Status: `ended`, winner_id = highest bidder, winning_bid_id set.
  │    └─ If highest bid < reserve_price (or no bids):
  │         └─ Status: `ended`, winner_id = null (reserve not met).
  └─ Dispatches `AuctionClosed` event.
       └─ Reverb broadcasts closing outcome & winner details to `private-auction.{id}`.
```

---

## 3. API Endpoints Map (`/api/v1`)

### Public Endpoints
| Method | Endpoint | Query Filters | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/auctions` | `status=live\|scheduled\|ended`, `category`, `search`, `sort`, `per_page` | Paginated public auction catalog |
| `GET` | `/api/v1/auctions/{idOrSlug}` | None | Detailed auction view with public bid history |

### Buyer Endpoints (`auth:sanctum`)
| Method | Endpoint | Middleware / Gate | Description |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/auctions/{auction}/bids` | `throttle:bidding` (30/min) | Place a bid on an active auction |
| `GET` | `/api/v1/buyer/bids` | None | View paginated list of bids placed by the buyer |
| `DELETE` | `/api/v1/buyer/bids/{bid}` | Owner only, active auction | Withdraw an active bid |

### Artisan Endpoints (`auth:sanctum`)
| Method | Endpoint | Policy Check | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/artisan/auctions` | Verified Artisan | List artisan's auctions with summary stats |
| `POST` | `/api/v1/artisan/auctions` | Verified Artisan | Create auction (`starts_at`, `ends_at`, `starting_price`, `reserve_price`) |
| `PATCH`| `/api/v1/artisan/auctions/{auction}`| Owner, No bids, Scheduled | Update auction details before start |
| `DELETE`| `/api/v1/artisan/auctions/{auction}/cancel`| Owner, Scheduled or Active | Cancel auction |

---

## 4. High-Concurrency Safeguards ("Bid Sniping")

Under extreme concurrency scenarios (e.g. hundreds of bids submitted in the closing seconds):
1. [BidService.php](file:///c:/laragon/www/Lamsa.ma/Modules/Auction/app/Services/BidService.php) acquires an exclusive pessimistic row lock on the `Auction` record using `lockForUpdate()` inside a MySQL `DB::transaction()`.
2. Incoming simultaneous requests are serialized by InnoDB.
3. Each request re-evaluates the bid against the **freshly committed database price**, preventing stale price overwrites or race conditions.
4. If a bid does not strictly exceed the highest existing bid, it aborts cleanly with `HTTP 422 Unprocessable Entity`.
5. The `bidding` rate limiter protects against automated spam scripts (30 bids/min per user).

---

## 5. Real-Time WebSockets & Broadcasting

The module broadcasts two real-time events implementing `ShouldBroadcastNow`:
1. **`BidPlaced`**: Broadcasts the new bid amount, bidder name, and timestamp.
2. **`AuctionClosed`**: Broadcasts the winning bidder, winning amount, and reserve met flag.

Both events broadcast to the private channel:
$$\text{private-auction.\{auction\_id\}}$$

### Channel Authorization
Channel authorization is encapsulated in [Modules/Auction/routes/channels.php](file:///c:/laragon/www/Lamsa.ma/Modules/Auction/routes/channels.php):
```php
Broadcast::channel('auction.{id}', function (User $user, int $id): bool {
    // Authenticated users with a valid Sanctum token can listen to live bids
    return true;
}, ['guards' => ['sanctum']]);
```

### Frontend Integration (Client-Side)
External frontend clients (Next.js, Vue, React, mobile) connect via `laravel-echo` and `pusher-js`:
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const echo = new Echo({
    broadcaster: 'reverb',
    key: process.env.NEXT_PUBLIC_REVERB_APP_KEY,
    wsHost: 'api.lamsa.ma',
    wsPort: 8080,
    authEndpoint: 'https://api.lamsa.ma/broadcasting/auth',
    auth: {
        headers: {
            Authorization: `Bearer ${userSanctumToken}`,
        },
    },
});

// Subscribe to the auction room:
echo.private(`auction.${auctionId}`)
    .listen('.BidPlaced', (event) => {
        console.log('New Bid:', event.amount, event.bidder_name);
    })
    .listen('.AuctionClosed', (event) => {
        console.log('Auction Ended! Winner:', event.winner_name);
    });
```

---

## 6. Automated Sweeper & Scheduled Command

The module provides [CloseExpiredAuctionsCommand.php](file:///c:/laragon/www/Lamsa.ma/Modules/Auction/app/Console/Commands/CloseExpiredAuctionsCommand.php):
```bash
php artisan auctions:close-expired
```

It invokes [AuctionService::closeExpired()](file:///c:/laragon/www/Lamsa.ma/Modules/Auction/app/Services/AuctionService.php), which:
* Sweeps all active auctions where `ends_at <= now()`.
* Evaluates bids against the confidential `reserve_price`.
* Transitions state to `ended` and broadcasts `AuctionClosed`.
* Is **100% idempotent**: Executing it repeatedly produces zero duplicate state changes or side effects.

### Scheduler Configuration
Configured in [routes/console.php](file:///c:/laragon/www/Lamsa.ma/routes/console.php):
```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('auctions:close-expired')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
```

---

## 7. Testing & Quality Assurance

Run all test suites for the Auction module:
```bash
# Run feature test suite (29 tests)
php artisan test Modules/Auction/tests/Feature

# Static analysis (Larastan Level 5)
./vendor/bin/phpstan analyse Modules/Auction

# Code style formatting (Laravel Pint)
./vendor/bin/pint Modules/Auction
```

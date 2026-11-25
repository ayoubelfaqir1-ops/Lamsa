# ADR 0002: Pessimistic Row Locking for Auctions and Checkout

- **Status:** Accepted
- **Date:** 2026-11-06
- **Deciders:** Engineering Architecture Team
- **Technical Context:** High-concurrency live auction bidding and atomic multi-item inventory checkout

---

## Context and Problem Statement

In an artisanal marketplace, items are frequently **unique, one-of-a-kind handcrafted pieces** (stock = 1). In live auctions, multiple buyers submit bids simultaneously during the final seconds of an auction.

Without proper concurrency controls, high-frequency simultaneous requests cause:
1. **Overselling / Stock Deficit:** Two buyers checkout the last item simultaneously; both requests read `stock = 1` before either writes, resulting in two completed orders for one physical product.
2. **Auction Bid Inversion:** Two simultaneous bids arrive for \$100. Both read `current_price = $90` and record \$100, corrupting the bidding history and leaving no clear single winner.

---

## Considered Options

1. **Option 1: Optimistic Locking (`version` column)**
   - *How it works:* Add a `version` column to `products` and `auctions`. Update with `WHERE version = $currentVersion`.
   - *Pros:* No database locks held; fast for read-heavy workloads with low collision probability.
   - *Cons:* During the frantic final 10 seconds of a live auction with 20 buyers bidding, 19 requests would fail and throw exceptions, creating a terrible user experience.
2. **Option 2: Redis Distributed Locks (`Cache::lock()`)**
   - *How it works:* Acquire a Redis key lock before querying MySQL.
   - *Pros:* Offloads lock coordination from MySQL.
   - *Cons:* Dual-store race conditions (split-brain between Redis and MySQL). Network partitions or Redis crashes leave stale locks or bypass safety.
3. **Option 3: Database Pessimistic Locking (`SELECT ... FOR UPDATE`)**
   - *How it works:* Run inside a MySQL InnoDB `DB::transaction()` and execute `lockForUpdate()`.
   - *Pros:* 100% native ACID serialization. Competing requests automatically wait in a hardware-level queue for milliseconds and re-evaluate the latest committed state.
   - *Cons:* Held locks can cause database deadlocks if transactions are slow or acquire locks in different orders.

---

## Decision Outcome

**Chosen Option:** Option 3 — **Pessimistic Row Locking (`lockForUpdate()`) inside strict InnoDB transactions**.

### Safeguards Implemented
- Transactions are kept **ultra-lean** (< 15ms): No HTTP calls, no email sending, and no external I/O occur inside the `DB::transaction()` block.
- Lock acquisition order is always deterministic (e.g. products are sorted by `id ASC` before calling `lockForUpdate()`) to mathematically eliminate deadlocks.
- All post-commit side effects (sending notifications, broadcasting Reverb events) are queued via `DB::afterCommit()`.


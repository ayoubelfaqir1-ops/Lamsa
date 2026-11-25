# ADR 0001: Modular Monolith Architecture over Microservices

- **Status:** Accepted
- **Date:** 2026-11-05
- **Deciders:** Engineering Architecture Team
- **Technical Context:** High-performance e-commerce and live auction platform for Moroccan artisans

---

## Context and Problem Statement

When designing Lamsa.ma, we needed an architectural pattern capable of supporting diverse business capabilities: user identity, store management, product catalog with review aggregation, shopping cart with atomic multi-vendor checkout, and high-concurrency live auctions.

We had to decide between:
1. **Independent Microservices** (Separate repos, distinct databases, gRPC/HTTP inter-service APIs).
2. **Traditional Monolith** (Single codebase, single database, shared models).
3. **Modular Monolith** (Single codebase and database, strictly encapsulated domain packages via `nwidart/laravel-modules`).

---

## Decision Drivers

- **ACID Transaction Guarantees:** Order checkout splits items across multiple artisan sub-orders in a single atomic transaction. In microservices, this would require 2-Phase Commit (2PC) or Sagas with eventual consistency.
- **Team Size & Velocity:** As an agile engineering team, running distributed Kubernetes clusters, service meshes, and cross-service CI/CD pipelines would consume excessive DevOps bandwidth.
- **Low Network Latency:** Real-time auction sweeps and product catalog queries cannot afford inter-service HTTP hops.

---

## Considered Options

1. **Option 1: Microservices**
   - *Pros:* Independent scaling and deployment per domain.
   - *Cons:* Distributed transaction complexity, network overhead, high infrastructure cost, eventual consistency lag.
2. **Option 2: Traditional Monolith**
   - *Pros:* Simple setup.
   - *Cons:* Code quickly degenerates into "spaghetti architecture" with cross-cutting model mutations and zero domain boundaries.
3. **Option 3: Modular Monolith**
   - *Pros:* High cohesion, clear domain boundaries, local in-memory method calls, single database ACID transactions, easy migration to microservices later if individual domains require separate scaling.
   - *Cons:* Requires discipline to avoid breaking module boundaries.

---

## Decision Outcome

**Chosen Option:** Option 3 — **Modular Monolith** (`nwidart/laravel-modules`).

### Consequences & Tradeoffs
- **Positive:** We retain single-transaction database rollbacks (if order item creation fails, the master order rolls back atomically).
- **Positive:** Local development requires only 1 repository clone and 1 database.
- **Negative:** Developers must maintain discipline by respecting module boundaries (no querying another module's internal models directly without proper foreign key abstraction).


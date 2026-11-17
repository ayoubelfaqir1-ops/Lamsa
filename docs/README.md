# Lamsa.ma Technical Documentation Portal

Welcome to the centralized engineering documentation for **Lamsa.ma** (لمسة), a modern, headless, modular REST API marketplace and live auction platform for Moroccan artisans.

---

## 🗺️ Documentation Directory

### 🏛️ 1. Architecture & System Design
Understand the high-level design, domain boundaries, database relationships, and key technical decisions.

- [**System Architecture Overview**](architecture/overview.md) — System topology, C4 context, runtime layers, tech stack justifications.
- [**Modular Monolith Architecture**](architecture/modular-monolith.md) — Encapsulation rules, domain isolation, folder conventions, and inter-module communication.
- [**Database Schema & Concurrency**](architecture/database-schema.md) — Global ER diagram, composite indexing, and row-level pessimistic locking (`lockForUpdate`).
- [**Security & Access Control**](architecture/security.md) — Role-Based Access Control (RBAC) matrix, Sanctum tokens, rate limiting, and trusted proxies.

#### 📜 Architecture Decision Records (ADRs)
- [**ADR 0001: Modular Monolith over Microservices**](architecture/decisions/0001-modular-monolith-over-microservices.md)
- [**ADR 0002: Pessimistic Row Locking for Auctions and Checkout**](architecture/decisions/0002-pessimistic-locking-for-auctions-and-orders.md)
- [**ADR 0003: Event-Driven Cache Invalidation over Tags**](architecture/decisions/0003-event-driven-cache-invalidation-over-tags.md)

---

### 💻 2. Development & Integration
Get your local environment running, understand code quality standards, and connect client frontends.

- [**Local Development Getting Started**](development/getting-started.md) — Prerequisites, step-by-step setup, seeding, and running local services.
- [**Coding Standards & Contribution**](development/coding-standards.md) — Laravel Pint rules, static analysis with Larastan, Git branching, and PR checklist.
- [**Testing Strategy & Execution**](development/testing-strategy.md) — Testing Trophy, subcutaneous feature tests, SQLite vs MySQL, running and writing tests.
- [**API Documentation (OpenAPI / Scramble)**](development/api-documentation.md) — Accessing `/docs/api`, "Try it out" explorer, and OpenAPI 3.1 JSON export.
- [**Frontend Integration Guide**](development/frontend-integration.md) — Sanctum Bearer tokens, Reverb WebSocket client setup, auto-generating TypeScript types, and error formats.

---

### 🚀 3. Operations & Production
Provision servers, configure background daemons, inspect environment variables, and respond to incidents.

- [**Production Deployment & Provisioning Checklist**](operations/production-checklist.md) — Ubuntu server setup, Nginx reverse proxy, Let's Encrypt SSL, Supervisor daemons, and optimizations.
- [**Environment Variables Reference**](operations/environment-variables.md) — Exhaustive reference table of every configuration key for local and production `.env`.
- [**CI/CD Pipeline & Quality Gates**](operations/ci-cd-pipeline.md) — GitHub Actions workflow breakdown (Gitleaks, Pint, Larastan, tests) and pre-deploy gates.
- [**Operational Runbooks & Troubleshooting**](operations/troubleshooting.md) — Incident playbooks for queue stalls, WebSocket disconnects, cache staleness, and S3 errors.

---

### 📦 4. Domain Modules Documentation
Each business domain maintains its own specialized 11-section documentation file inside its module directory:

- [**Auth Module**](../Modules/Auth/README.md) — Identity, Sanctum, RBAC, OAuth account takeover safeguards, signed email verification.
- [**Store Module**](../Modules/Store/README.md) — Artisan stores, slug collision handling, S3 media uploads.
- [**Product Module**](../Modules/Product/README.md) — Catalog browsing, category tree, reviews aggregation, favorites wishlist.
- [**Order Module**](../Modules/Order/README.md) — Shopping cart, atomic checkout with row-locking, multi-vendor sub-order splitting.
- [**Auction Module**](../Modules/Auction/README.md) — Scheduled/live auctions, real-time WebSocket bidding, automated closing sweeper.
- [**CMS Module**](../Modules/CMS/README.md) — Editorial content (Terms, Privacy, FAQ), column projection, automated observer caching.


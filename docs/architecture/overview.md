# System Architecture Overview

> High-level architecture, runtime execution model, and infrastructure topology for the Lamsa.ma artisan marketplace backend.

---

## 1. Architectural Philosophy

Lamsa.ma is built as a **Headless Modular Monolith**:
- **Headless / API-First**: The backend is strictly an HTTP/WebSocket API. It renders zero server-side HTML/Blade views, delegating the user interface entirely to decoupled frontends (Web: Next.js/React, Mobile: Flutter/React Native).
- **Modular Monolith**: Code is partitioned into distinct business domains (`Modules/*`), enforcing clear boundaries and single-responsibility principles without the operational complexity and network latency of microservices.
- **Real-Time Enabled**: Live bidding during auctions uses native WebSockets via Laravel Reverb rather than aggressive client HTTP polling.

---

## 2. Global Architecture Diagram

```mermaid
flowchart TD
    subgraph Clients["Clients Layer (Decoupled)"]
        Web["Next.js Web Client\n(TypeScript)"]
        Mobile["Flutter / React Native Mobile\n(iOS / Android)"]
    end

    subgraph Perimeter["Perimeter & Edge Layer"]
        Cloudflare["Cloudflare CDN & DDoS Shield\n(SSL Termination / Edge Caching)"]
        Nginx["Nginx Reverse Proxy\n(HTTP/2, FastCGI, Security Headers)"]
    end

    subgraph AppServer["Application Layer (PHP 8.2+ FPM)"]
        Router["Kernel & Global Middleware\n(Sanctum, Throttle, TrustProxies)"]
        
        subgraph Domains["Encapsulated Domain Modules"]
            AuthMod["Auth Module\n(Sanctum, RBAC, OAuth)"]
            StoreMod["Store Module\n(Profiles, Slugs, Media)"]
            ProdMod["Product Module\n(Catalog, Categories, Reviews)"]
            OrderMod["Order Module\n(Cart, Atomic Checkout, Splitting)"]
            AuctMod["Auction Module\n(Bidding Engine, Sweeper)"]
            CMSMod["CMS Module\n(Editorial Content, Cache)"]
        end
    end

    subgraph State["State & Persistence Infrastructure"]
        MySQL[("MySQL 8.0 Primary\n(InnoDB, Row-Level Locking)")]
        Redis[("Redis 7.x In-Memory\n(Cache Store, Job Queue, Rate-Limiting)")]
        S3["AWS S3 Bucket\n(Store Logos & Product Images)"]
        Reverb["Laravel Reverb Server\n(WebSockets :8080)"]
    end

    Web & Mobile -->|HTTPS API Requests| Cloudflare
    Web & Mobile <-->|WSS Real-time Bids| Reverb
    Cloudflare -->|Reverse Proxy / X-Forwarded-For| Nginx
    Nginx -->|FastCGI Socket /run/php/php8.2-fpm.sock| Router
    Router --> Domains

    Domains -->|ACID Transactions & Locking| MySQL
    Domains -->|Cache Read/Write & Queue Push| Redis
    Domains -->|Image Uploads / Deletions| S3
    Domains -->|Broadcast Events (BidPlaced)| Reverb
```

---

## 3. Technology Stack & Decision Rationale

| Layer | Technology | Decision Rationale & Tradeoffs |
| :--- | :--- | :--- |
| **Runtime** | PHP 8.2+ / PHP-FPM | Modern typed PHP with JIT, low memory footprint, and OpCache shared bytecode memory. |
| **Framework** | Laravel 12.x | Expressive ecosystem with robust database transactions, queued jobs, first-party WebSockets, and active security maintenance. |
| **Architecture** | Modular Monolith (`nwidart/laravel-modules`) | Gives microservice-like modular isolation while retaining the simplicity of single-transaction ACID guarantees and a unified codebase. |
| **Primary Database** | MySQL 8.0 (InnoDB) | Strict relational constraints, foreign keys, ACID transactions, and row-level pessimistic locking (`SELECT ... FOR UPDATE`). |
| **In-Memory Store** | Redis 7.x | Ultra-low latency (<1ms) in-memory storage for high-frequency cache, session data, and zero-overhead queue polling. |
| **WebSockets** | Laravel Reverb | First-party, event-driven WebSocket server capable of thousands of concurrent connections without third-party SaaS fees (e.g. Pusher). |
| **Media Storage** | AWS S3 via Flysystem | Stateless cloud storage for media; allows horizontal scaling of web servers without NFS file syncing. |
| **Web Server** | Nginx | High-performance reverse proxy for SSL offloading, gzip/brotli compression, HTTP/2 multiplexing, and proxy security headers. |

---

## 4. Cross-Cutting Infrastructure Patterns

### A. Health Monitoring Probe
The application exposes an unauthenticated health probe at `GET /api/v1/health` used by load balancers, Docker, and uptime monitors. It actively checks:
1. **Database**: Executes `SELECT 1` and measures round-trip latency.
2. **Cache**: Writes, reads, and forgets a temporary probe key.
3. **Storage**: Writes, reads, and cleans up an S3 canary file.

Returns `200 OK` when all subsystems are operational, or `503 Service Unavailable` with details if any subsystem is degraded.

### B. Security & Rate Limiting
- **Token Authentication**: Laravel Sanctum personal access tokens (`Bearer <token>`) with hashed database storage.
- **Rate-Limiting (Throttling)**: Sliding-window rate limiters prevent brute-force attacks on auth endpoints (`throttle:60,1`) and platform scraping.
- **Trusted Proxies**: Configured to trust CDN / reverse-proxy headers (`X-Forwarded-For`, `X-Forwarded-Proto`) to prevent client IP spoofing and ensure correct rate limiting.


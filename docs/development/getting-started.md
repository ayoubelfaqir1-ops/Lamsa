# Local Development Getting Started Guide

> Complete onboarding guide for setting up, running, and verifying the Lamsa.ma backend locally.

---

## 1. Prerequisites

Before setting up Lamsa, ensure your local workstation has the following installed:

- **PHP 8.2 or 8.3**
  - Required extensions: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `gd`, `bcmath`, `xml`, `zip`, `pdo_sqlite`
- **Composer 2.x** ([getcomposer.org](https://getcomposer.org))
- **Node.js 18+ & npm** (for Playwright API E2E testing)
- **Python 3.10+** (optional, for Schemathesis contract fuzzing)
- **MySQL 8.0+** or MariaDB 10.5+ (e.g. via Laragon, Docker, or native service)
- **Git**
- *(Optional)* **Redis** (local development can use `CACHE_STORE=database`)

---

## 2. Step-by-Step Installation

### Step 1: Clone the Repository
```bash
git clone https://github.com/ayoubelfaqir1-ops/Lamsa.git
cd Lamsa
```

### Step 2: Install PHP & Node Dependencies
```bash
composer install
npm install
```
*Under the hood: Composer installs backend PHP packages into `vendor/`, and npm installs the Playwright E2E testing framework into `node_modules/`.*

### Step 3: Configure Environment
Copy the example environment file and generate a unique cryptographic application key:
```bash
cp .env.example .env
php artisan key:generate
```

### Step 4: Configure Local Database
Open `.env` in your editor and configure your local MySQL credentials:
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lamsa
DB_USERNAME=root
DB_PASSWORD=
```
*(Ensure the database `lamsa` exists in your MySQL instance).*

### Step 5: Run Database Migrations & Seeders
```bash
php artisan migrate --seed
```
*What this does:*
- Creates all core and modular database tables.
- Seeds default roles (`admin`, `artisan`, `buyer`).
- Seeds demo categories, artisan stores, products, and CMS pages.

### Step 6: Create Storage Symlink
```bash
php artisan storage:link
```
*What this does: Creates a symbolic link from `public/storage` to `storage/app/public` so locally uploaded artisan logos are viewable in the browser.*

---

## 3. Running the Local Services

To run the complete platform locally, open 3 terminal tabs:

### Terminal 1: HTTP API Server
```bash
php artisan serve
```
The REST API is now live at: `http://127.0.0.1:8000`

### Terminal 2: Background Queue Worker
```bash
php artisan queue:work
```
Listens for asynchronous jobs (e.g. sending emails, processing image resizing, closing expired auctions).

### Terminal 3: WebSocket Server (Laravel Reverb)
```bash
php artisan reverb:start --debug
```
Starts the local WebSocket daemon on `127.0.0.1:8080` for real-time live auction bidding.

---

## 4. Verification Checklist

Run these commands to verify that your local environment is functioning correctly:

1. **Verify Health Probe**:
   ```bash
   curl http://127.0.0.1:8000/api/v1/health
   ```
   Expected response:
   ```json
   {
     "status": "healthy",
     "checks": {
       "database": { "status": "up" },
       "cache": { "status": "up" },
       "storage": { "status": "up" }
     }
   }
   ```

2. **Run Internal Feature & Unit Tests**:
   ```bash
   php artisan test
   ```
   All 201 tests across the core app and all 6 modules should pass (green).

3. **Run Automated API-Level E2E Journeys**:
   ```bash
   npm run test:e2e
   ```
   Executes the 3 multi-step business journeys over the wire against `http://127.0.0.1:8000`.

4. **Check Code Formatting**:
   ```bash
   vendor/bin/pint --test
   ```
   Should report: `{"tool":"pint","result":"passed"}`.


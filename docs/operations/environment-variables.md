# Environment Variables Reference

> Exhaustive guide to all environment variables used across local development and production in Lamsa.ma.

---

## 1. Application Core Configuration

| Variable | Local Default | Production Default | Description & Allowed Values |
| :--- | :--- | :--- | :--- |
| `APP_NAME` | `Lamsa` | `Lamsa` | Display name of the application. |
| `APP_ENV` | `local` | `production` | Environment mode (`local`, `testing`, `staging`, `production`). In production, error stack traces are suppressed. |
| `APP_KEY` | *(Generated)* | *(Generated)* | 32-character AES-256 encryption key generated via `php artisan key:generate`. |
| `APP_DEBUG` | `true` | `false` | **Critical Security:** Must be `false` in production to prevent leaking database credentials in stack traces. |
| `APP_URL` | `http://127.0.0.1:8000` | `https://api.lamsa.ma` | Root URL used for generating signed links (e.g. email verification). |

---

## 2. Database (MySQL / MariaDB)

| Variable | Local Default | Production Default | Description |
| :--- | :--- | :--- | :--- |
| `DB_CONNECTION` | `mysql` | `mysql` | Database driver (`mysql`, `sqlite` for tests). |
| `DB_HOST` | `127.0.0.1` | `127.0.0.1` or RDS endpoint | Host address of MySQL server. |
| `DB_PORT` | `3306` | `3306` | Database TCP port. |
| `DB_DATABASE` | `lamsa` | `lamsa_production` | Target database name. |
| `DB_USERNAME` | `root` | `lamsa_user` | MySQL user. |
| `DB_PASSWORD` | *(empty)* | *(Strong password)* | Database password. |

---

## 3. Caching, Sessions, & Queues (Redis)

| Variable | Local Default | Production Default | Description |
| :--- | :--- | :--- | :--- |
| `CACHE_STORE` | `database` | `redis` | Cache backing store. In prod, `redis` provides <1ms lookups. |
| `QUEUE_CONNECTION` | `database` | `redis` | Queue backend. In prod, `redis` handles high-throughput jobs with zero DB locks. |
| `SESSION_DRIVER` | `database` | `redis` | Session storage driver. |
| `REDIS_CLIENT` | `phpredis` | `phpredis` | PHP client extension (`phpredis` C extension or `predis`). |
| `REDIS_HOST` | `127.0.0.1` | `127.0.0.1` | Redis server address. |
| `REDIS_PORT` | `6379` | `6379` | Default Redis TCP port. |
| `REDIS_PASSWORD` | `null` | *(Secure password)* | Redis AUTH password. |

---

## 4. Media Storage (AWS S3 & CloudFront)

| Variable | Local Default | Production Default | Description |
| :--- | :--- | :--- | :--- |
| `FILESYSTEM_DISK` | `local` | `local` | Internal filesystem disk for private application storage. |
| `MEDIA_DISK` | `public` | `s3` | Public media disk for artisan store logos and product images. |
| `AWS_ACCESS_KEY_ID` | `null` | `AKIA...` | IAM user Access Key with `s3:PutObject`, `s3:DeleteObject`, and `s3:GetObject` permissions. |
| `AWS_SECRET_ACCESS_KEY` | `null` | *(Secret)* | IAM user Secret Access Key. |
| `AWS_DEFAULT_REGION` | `eu-west-3` | `eu-west-3` | AWS Region (e.g. Paris `eu-west-3` for Moroccan latency). |
| `AWS_BUCKET` | `null` | `lamsa-media-production` | S3 bucket name. |
| `AWS_URL` | `null` | `https://cdn.lamsa.ma` | Custom CDN endpoint or CloudFront distribution domain. |

---

## 5. Real-Time WebSockets (Laravel Reverb)

| Variable | Local Default | Production Default | Description |
| :--- | :--- | :--- | :--- |
| `BROADCAST_CONNECTION` | `reverb` | `reverb` | Event broadcasting driver. |
| `REVERB_APP_ID` | `lamsa-app-id` | *(Random alphanumeric)* | Unique Reverb application identifier. |
| `REVERB_APP_KEY` | `lamsa-key` | *(Random alphanumeric)* | Public key shared with client frontends. |
| `REVERB_APP_SECRET` | `lamsa-secret`| *(Secure secret)* | Private secret key used to sign private channel tokens. |
| `REVERB_HOST` | `127.0.0.1` | `ws.lamsa.ma` | Public WebSocket hostname. |
| `REVERB_PORT` | `8080` | `443` | WebSocket port (`443` via Nginx WSS proxy in production). |
| `REVERB_SCHEME` | `http` | `https` | Protocol scheme (`https` in production). |

---

## 6. Transactional Mail (SMTP)

| Variable | Local Default | Production Default | Description |
| :--- | :--- | :--- | :--- |
| `MAIL_MAILER` | `log` | `smtp` | Mail driver (`log` for local testing, `smtp` in prod). |
| `MAIL_HOST` | `127.0.0.1` | `smtp-relay.brevo.com` | SMTP relay server (e.g. Brevo, SendGrid, Amazon SES). |
| `MAIL_PORT` | `1025` | `587` | SMTP port (587 TLS). |
| `MAIL_USERNAME` | `null` | *(SMTP username)* | Authenticated email sender account. |
| `MAIL_PASSWORD` | `null` | *(SMTP password)* | SMTP API or account key. |
| `MAIL_FROM_ADDRESS` | `noreply@lamsa.ma` | `noreply@lamsa.ma` | Sender email address on verification and order emails. |
| `MAIL_FROM_NAME` | `Lamsa Marketplace`| `Lamsa Marketplace` | Sender display name. |


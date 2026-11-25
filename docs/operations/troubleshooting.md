# Operational Runbooks & Troubleshooting

> Incident response playbooks, failure diagnostics, and step-by-step remediation procedures for Lamsa.ma.

---

## 1. Background Queue Failures & Worker Stalls

### Symptoms:
- Order confirmation emails or auction close notifications are not being sent.
- Jobs are accumulating in the `jobs` table or Redis queue.

### Diagnostic Steps:
1. **Check Supervisor Worker Status:**
   ```bash
   sudo supervisorctl status
   ```
   If workers are in `FATAL` or `BACKOFF` state, check logs:
   ```bash
   tail -n 100 /var/log/supervisor/lamsa-worker.log
   ```
2. **List Failed Jobs:**
   ```bash
   php artisan queue:failed
   ```
   Inspect the exception trace to identify whether the failure is an SMTP timeout, a dead API key, or a database constraint.

### Remediation:
- **Restart workers after code deploys:**
  ```bash
  php artisan queue:restart
  sudo supervisorctl restart all
  ```
- **Retry all failed jobs after fixing root cause:**
  ```bash
  php artisan queue:retry all
  ```
- **Flush unrecoverable poison-pill jobs:**
  ```bash
  php artisan queue:flush
  ```

---

## 2. WebSocket / Reverb Disconnection Issues

### Symptoms:
- Live auction bids do not appear automatically in the frontend.
- Frontend browser console shows: `WebSocket connection to 'wss://ws.lamsa.ma' failed`.

### Diagnostic Steps:
1. **Verify Reverb Process is Alive:**
   ```bash
   sudo supervisorctl status lamsa-reverb
   # Or check listening port
   sudo netstat -tlpn | grep 8080
   ```
2. **Verify Nginx WebSocket Proxy Configuration:**
   Ensure Nginx has HTTP/1.1 and upgrade headers configured:
   ```nginx
   proxy_http_version 1.1;
   proxy_set_header Upgrade $http_upgrade;
   proxy_set_header Connection "Upgrade";
   ```
3. **Verify Channel Authorization (403 Forbidden):**
   If connecting to `private-auction.{id}` returns 403, check `routes/channels.php`. Verify the client is passing a valid `Authorization: Bearer <token>` in the Echo auth headers.

---

## 3. Cache Staleness or Desynchronization

### Symptoms:
- Categories or CMS pages show old titles even after being edited in the database.

### Diagnostic Steps:
1. Check if model observers are registered in `ProductServiceProvider` and `CMSServiceProvider`.
2. Inspect the raw cached value in Redis or MySQL:
   ```bash
   # If using Redis:
   redis-cli GET lamsa_database_active_categories_list
   ```

### Remediation:
- **Purge only application data cache (Safe in production):**
  ```bash
  php artisan cache:forget active_categories_list
  php artisan cache:forget cms_pages_list
  ```
- **Never run `php artisan cache:clear` carelessly in production** if `SESSION_DRIVER=redis`, as this will log out all active users! Clear only specific tags or keys.

---

## 4. Database Deadlocks (`Lock wait timeout exceeded`)

### Symptoms:
- API returns `500 Internal Server Error` during high-volume auction bidding or peak checkout moments.
- MySQL error log contains: `Deadlock found when trying to get lock; try restarting transaction`.

### Diagnostic Steps:
1. **Inspect InnoDB Status:**
   ```sql
   SHOW ENGINE INNODB STATUS;
   ```
   Scroll to the `LATEST DETECTED DEADLOCK` section to see the two competing SQL queries and locked row IDs.

### Remediation:
- Verify that both transactions acquire locks in the exact same sorted order (`id ASC`).
- Verify no slow network I/O or email sending occurs inside the `DB::transaction()` closure.

---

## 5. Media Upload Failures & S3 403 Forbidden

### Symptoms:
- Artisan store logo upload fails with `Unable to write file at location: stores/logos/...`.

### Diagnostic Steps:
1. **Local Environment:**
   Ensure the storage directory is linked and writable:
   ```bash
   php artisan storage:link
   ```
2. **Production S3 Environment:**
   - Verify AWS IAM credentials in `.env`:
     `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, `AWS_DEFAULT_REGION`.
   - Verify IAM policy allows:
     `s3:PutObject`, `s3:GetObject`, `s3:DeleteObject`, `s3:PutObjectAcl` on `arn:aws:s3:::lamsa-media-production/*`.
   - Test connectivity using our health check:
     ```bash
     curl -s https://api.lamsa.ma/api/v1/health | grep storage
     ```

---

## 6. E2E Test Authentication Failures (`422 Credentials do not match`)

### Symptoms:
- Running `npm run test:e2e` fails with:
  `Failed to login as buyer: 422 {"message": "The provided credentials do not match our records."}`

### Diagnostic Steps:
- Unlike PHPUnit feature tests which run on in-memory SQLite, Playwright E2E tests are out-of-process and connect to your **real MySQL database**.
- Check if your local MySQL database has users:
  ```bash
  php artisan tinker --execute="echo Modules\Auth\Models\User::count();"
  ```
  If it outputs `0`, the database is completely empty.

### Remediation:
- Rebuild and seed your local MySQL database with demo accounts:
  ```bash
  php artisan migrate:fresh --seed
  ```

---

## 7. WebSocket Port 8080 Collision (`Pusher error: Bad Format`)

### Symptoms:
- Bidding on an auction returns: `500 Server Error` or `422 Pusher error: Bad Format.`

### Diagnostic Steps:
- When a bid is placed, Laravel broadcasts `BidPlaced` to the WebSocket server over port `8080`.
- On many developer workstations, port `8080` is already in use by another software (such as Java, Apache Tomcat, or Jenkins).
- Check which process owns port 8080:
  ```powershell
  Get-Process -Id (Get-NetTCPConnection -LocalPort 8080).OwningProcess
  ```

### Remediation:
- **Option A (Safest for local API testing):** Set broadcasting to log in `.env`:
  ```env
  BROADCAST_CONNECTION=log
  ```
- **Option B (Run Reverb on dedicated port):** Change port to `8085` in `.env`:
  ```env
  REVERB_PORT=8085
  ```
  And start Reverb with: `php artisan reverb:start --port=8085`.


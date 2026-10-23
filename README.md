# OTP Doctor — SMS Verification Marketplace & Super Admin Platform

Production-ready Core PHP 8.2+ and MySQL 8+ SMS OTP service platform with mobile-friendly User Panel, single Super Admin Console, live provider integration with OTP Doctor API, atomic wallet ledger, Razorpay checkout, manual QR top-ups, automated order cancellation, and background cron processing.

---

## 1. Locked Technology Stack & Architecture

- **Backend**: Core PHP 8.2+ (strictly framework-free, standard clean architecture)
- **Database**: MySQL 8.0+ with InnoDB, Foreign Keys, Unique Constraints, and `DECIMAL(12,4)` financial columns
- **Database Layer**: Native PHP PDO with prepared statements (`ATTR_EMULATE_PREPARES => false`)
- **Frontend**: HTML5, Tailwind CSS, Alpine.js, SweetAlert2, JetBrains Mono tabular figures
- **HTTP/API Client**: Native PHP cURL with SSL verification, timeouts, and secret-redacted DB logging
- **External Provider**: OTP Doctor SMS API (`https://www.otpdoctor.in/stubs/handler_api.php`)
- **Real-time / Automation**: Background Cron Jobs, safe file-lock synchronization, and real-time polling with WebSocket fallback

---

## 2. Directory Structure

```
otp-platform/
├── app/
│   ├── Controllers/         # AuthController, OrderController, WalletController, AdminController
│   ├── Core/                # Database, Router, View, Auth, Session, CSRF, Logger
│   ├── Middleware/          # AuthMiddleware, SuperAdminMiddleware
│   ├── Services/            # OrderService, WalletService, SyncService
│   └── Helpers/
├── config/
│   ├── app.php              # Application settings, timezone, currency, timeouts
│   └── database.php         # PDO connection parameters
├── database/
│   ├── schema.sql           # Complete MySQL 8 InnoDB schema
│   ├── indexes.sql          # Performance indexes for orders, wallet, and providers
│   ├── seed.sql             # Default provider, services, countries, and payment gateways
│   └── seed_super_admin.php # Secure CLI script to provision single Super Admin
├── modules/
│   ├── Providers/
│   │   ├── ProviderInterface.php   # Provider contract
│   │   └── OtpDoctorProvider.php   # OTP Doctor handler implementation
│   └── Payments/
│       ├── PaymentGatewayInterface.php
│       ├── RazorpayService.php     # Native cURL + HMAC-SHA256 signature verification
│       └── ManualQrService.php     # Manual QR top-up & approval review workflows
├── public/                  # WEB DOCUMENT ROOT
│   ├── index.php            # Application front-controller & autoloader
│   ├── .htaccess            # Apache rewrite rules & security headers
│   └── assets/
│       ├── css/custom.css   # Clean typography & scrollbars
│       └── js/app.js        # Global CSRF fetch helper
├── resources/
│   └── views/
│       ├── auth/            # login.php, register.php
│       ├── layouts/         # user_layout.php, admin_layout.php
│       ├── user/            # services.php, order.php, orders.php, wallet.php
│       ├── admin/           # dashboard.php, providers.php, services.php, orders.php, users.php, payments.php
│       └── errors/          # 403.php, 404.php, 500.php
├── websocket/
│   ├── server.php           # Native PHP stream socket server
│   └── Broadcast.php        # Real-time event emitter
├── cron/
│   ├── sync_services.php    # Hourly catalog & country sync
│   ├── check_orders.php     # Active order status & OTP check
│   ├── expire_orders.php    # 15-minute countdown timeout & automatic refund
│   └── daily_reports.php    # Daily delivery & revenue audit
├── storage/
│   └── logs/app.log         # Redacted error & activity logs
└── .env.example
```

---

## 3. Server Requirements

- PHP >= 8.2 (PHP 8.3 recommended)
- MySQL >= 8.0
- Required PHP Extensions:
  - `pdo`
  - `pdo_mysql`
  - `curl`
  - `mbstring`
  - `json`
  - `openssl`
- Apache (with `mod_rewrite`) or Nginx

---

---

## 4. Installation & Setup

### Option A: Web-Based Installation Wizard (Recommended)

You do **not** need to run any command-line commands to install the platform. The system features a complete, browser-based 8-step wizard:

1. Point your domain or virtual host to the `public/` directory.
2. Open your browser and navigate to:
   ```
   https://yourdomain.com/install
   ```
   *(If the website is not yet installed, visiting any page will automatically redirect you to `/install`).*
3. Follow the 8-step wizard:
   - **Step 1: Welcome Page**: Overview and preparation checklist.
   - **Step 2: Server Requirements**: Live checks for PHP >= 8.2, PDO, cURL, OpenSSL, Mbstring, and folder write permissions.
   - **Step 3: Database Configuration**: Enter your MySQL host, port, database name, and user credentials. Click **Test Connection** to verify database connectivity.
   - **Step 4: Website Configuration**: Set your site name, auto-detected domain URL, timezone, and currency.
   - **Step 5: Admin Account Creation**: Enter the Single Super Admin email and secure password (minimum 8 characters).
   - **Step 6 & 7: Automated Installation**: Automatically builds all tables (`schema.sql`), performance indexes (`indexes.sql`), base verification services (`seed.sql`), writes `config/database.php` and `.env`.
   - **Step 8: Installation Complete**: Creates the permanent installation lock (`storage/installed.lock`), blocks subsequent installer access, and displays direct links to the website and admin panel.

---

### Option B: Command-Line (CLI) Setup (For Advanced Administrators)

If preferred, you may also install the application via CLI:

```bash
# 1. Create database and import schema
mysql -u root -p -e "CREATE DATABASE otp_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p otp_platform < database/schema.sql
mysql -u root -p otp_platform < database/indexes.sql
mysql -u root -p otp_platform < database/seed.sql

# 2. Provision the single Super Admin
php database/seed_super_admin.php admin@otpdoctor.in YourAdminPassword123 "Super Administrator"

# 3. Create lock file
touch storage/installed.lock
```

---

## 5. Web Server Virtual Host Configuration

The web server document root **MUST** point to the `public/` directory.

### Nginx Configuration
```nginx
server {
    listen 80;
    server_name otp.yourdomain.com;
    root /var/www/otp-platform/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(ht|env|git) {
        deny all;
    }
}
```

### Apache Configuration
Ensure `AllowOverride All` is active for `/var/www/otp-platform/public` and `mod_rewrite` is enabled. The included `public/.htaccess` routes all traffic to `index.php`.

---

## 6. Scheduled Cron Jobs Configuration

Add the following entries to the system crontab (`crontab -e`):

```cron
# Check active orders for incoming OTPs every minute
* * * * * php /var/www/otp-platform/cron/check_orders.php >> /var/www/otp-platform/storage/logs/cron.log 2>&1

# Expire orders older than 15 minutes and issue 100% automatic wallet refunds
*/2 * * * * php /var/www/otp-platform/cron/expire_orders.php >> /var/www/otp-platform/storage/logs/cron.log 2>&1

# Sync catalog services and countries hourly
0 * * * * php /var/www/otp-platform/cron/sync_services.php >> /var/www/otp-platform/storage/logs/cron.log 2>&1

# Daily financial and volume report at midnight
0 0 * * * php /var/www/otp-platform/cron/daily_reports.php >> /var/www/otp-platform/storage/logs/cron.log 2>&1
```

Each cron script features safe file-locking (`flock`) to prevent overlapping executions during high provider latency.

---

## 7. External SMS Provider: OTP Doctor API

The platform implements the documented OTP Doctor stub:
`https://www.otpdoctor.in/stubs/handler_api.php`

Supported actions:
- `getBalance`: Checks supplier account balance
- `getCountries`: Fetches active country prefixes
- `getServices`: Fetches available platforms and costs for a given country
- `getNumber`: Requests phone number assignment
- `getStatus`: Polls for SMS delivery (`STATUS_WAIT_CODE`, `STATUS_OK:code`)
- `setStatus`: Cancels number release (status 8)

All requests originate server-side via `App\Modules\Providers\OtpDoctorProvider`. All outbound calls and durations are logged to the `provider_logs` database table with credentials automatically redacted.

---

## 8. Payment Gateways & Wallet Security

### Razorpay Integration
- Configure Key ID, Key Secret, and Webhook Secret in Admin Panel -> Payment Gateways.
- Uses server-side HMAC-SHA256 signature verification (`hash_equals`).
- Client cannot credit wallet without server validation.

### Manual QR Payment
- Displays UPI ID and QR code to users.
- User submits payment amount and transaction reference (UTR).
- Placed in Super Admin review queue at `/admin/payments`.
- Super Admin verifies independently before atomic wallet credit.

---

## 9. Security Features

1. **Strict PDO prepared statements** preventing SQL injection.
2. **Session ID regeneration** upon login.
3. **Automatic CSRF validation** for state-changing POST requests.
4. **HTML output escaping** across all view templates.
5. **No plain-text credentials** in logs or client-facing responses.
6. **Immutable wallet ledger** with database transaction rollbacks on failure.

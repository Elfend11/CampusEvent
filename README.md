# CampusEvent

Integrated Campus Event & Ticketing Management System.

- `backend/` — Laravel 12 REST API (PHP 8.3.x, MySQL InnoDB, Sanctum cookie-based SPA auth)
- `frontend/` — Vue.js 3 + Vite + Pinia + Axios
- `docs/` — REQUIREMENTS, ARCHITECTURE, RULES, TASKS

## Requirements

- PHP 8.3.x with `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`, `fileinfo`
- Composer
- Node.js 20+ and npm
- MySQL 8.x (InnoDB)

## Backend setup

```sh
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Configure the database credentials in `backend/.env`. For email OTP delivery,
set the Gmail SMTP values in `backend/.env` (use a Google App Password, never the
normal account password):

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-account@gmail.com
MAIL_PASSWORD=your-google-app-password
MAIL_FROM_ADDRESS="your-account@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Gmail SMTP setup:

1. Enable 2-Step Verification on the Gmail account that will send OTP emails.
2. Open Google's App Password page and create a password named for CampusEvent.
3. Set `MAIL_USERNAME` to the full Gmail address.
4. Set `MAIL_PASSWORD` to the 16-character Google App Password. Do not use the
   normal Google account password.
5. Set `MAIL_FROM_ADDRESS` to the same Gmail address to avoid sender mismatch.
6. Run `php artisan config:clear` after changing mail values.
7. Start with Mailtrap or Laravel's `array`/`log` mailer for local testing if real
   Gmail delivery is not needed. Do not commit the real credentials.

Optional OTP policy overrides:

```dotenv
EMAIL_OTP_EXPIRES_MINUTES=10
EMAIL_OTP_RESEND_COOLDOWN_SECONDS=60
EMAIL_OTP_MAX_ATTEMPTS=5

SEED_SUPER_ADMIN_NAME="Super Admin CampusEvent"
SEED_SUPER_ADMIN_EMAIL="your-admin@gmail.com"
SEED_SUPER_ADMIN_PASSWORD="your-local-admin-password"
```

`SEED_SUPER_ADMIN_PASSWORD` must be configured only in `backend/.env` and must not
be committed. The database seeder creates or updates exactly one idempotent
`SUPER_ADMIN` account and does not create demo student, organization-admin, or
check-in-officer accounts.

Create the databases and set the credentials in `backend/.env`:

```sql
CREATE DATABASE campusevent CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE campusevent_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```sh
php artisan migrate
php artisan db:seed
php artisan serve --host=127.0.0.1 --port=8000
```

The seeder creates only one Super Admin account from `backend/.env`.
The account is marked email-verified and can log in immediately.

Students can create their own account from the frontend registration page
(`/register`), which calls `POST /api/v1/auth/register`. Registration does not
log the user in. A six-digit OTP is emailed and must be entered at
`/verify-email`; only verified accounts can log in.

Authentication endpoints:

- `POST /api/v1/auth/register` — create an unverified student account and send OTP.
- `POST /api/v1/auth/verify-otp` — verify the email with the OTP.
- `POST /api/v1/auth/resend-otp` — resend the OTP after the cooldown.
- `POST /api/v1/auth/login` — rejected with `403 EMAIL_UNVERIFIED` until verified.
- `POST /api/v1/auth/logout` and `GET /api/v1/auth/user` — session endpoints.

Accounts created by an administrator through `POST /api/v1/users` are treated as
trusted and marked verified immediately. Accounts that existed before email
verification was enabled are backfilled as verified by migration.

## Frontend setup

```sh
cd frontend
npm install
npm run dev
```

The Vite dev server runs on `http://localhost:5173` and proxies `/api` and
`/sanctum` to `http://127.0.0.1:8000`.

## Health check

```sh
curl http://127.0.0.1:8000/api/v1/health
```

## Tests

```sh
cd backend
php artisan test
```

### Concurrency and hard-problem tests

These tests run against a real MySQL InnoDB database and spawn concurrent OS
processes. See `docs/HARD_PROBLEM.md` for the full diagnostic and measured
evidence.

```sh
cd backend
php tests/Concurrency/run.php          # 100 registrations for 5 tickets (paid and free)
php tests/Concurrency/run_payment.php  # concurrent confirmations of one payment
php tests/Concurrency/run_checkin.php  # concurrent scans of one QR token
php tests/Concurrency/run_expiry.php   # concurrent reservation expiration
```

Tests use the `campusevent_test` MySQL database. The database name and connection
are set in `backend/phpunit.xml`; host, port, username, and password are read from
your local environment or CI secrets, so no database credentials are committed.
Set `DB_HOST`, `DB_PORT`, `DB_USERNAME`, and `DB_PASSWORD` before running the suite.

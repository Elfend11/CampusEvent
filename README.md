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

Create the databases and set the credentials in `backend/.env`:

```sql
CREATE DATABASE campusevent CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE campusevent_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```sh
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8000
```

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

Tests use the `campusevent_test` MySQL database (see `backend/phpunit.xml`).

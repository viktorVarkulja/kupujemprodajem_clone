# KupujemProdajem Clone (Laravel + Vue + Inertia)

Simple marketplace prototype built on Laravel 12, Vue 3, Inertia, Vite, Tailwind.

- Backend: Laravel 12 (PHP 8.2+)
- Frontend: Vue 3 + TypeScript via Inertia + Vite
- Auth: Laravel Fortify
- Styling: Tailwind CSS

## Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+ and npm (or yarn/pnpm)
- SQLite (recommended for quick start) or MySQL

## Quick Start (SQLite)

Run these from the project root:

1) Install PHP deps and create `.env`

```
composer install
cp .env.example .env   # PowerShell: copy .env.example .env
php artisan key:generate
```

2) Use SQLite (fastest):

```
# PowerShell (Windows)
New-Item -ItemType File -Force database/database.sqlite | Out-Null

# Bash (macOS/Linux)
mkdir -p database && : > database/database.sqlite

# In .env set:
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

3) Migrate and seed sample data (categories + demo user):

```
php artisan migrate --seed
```

4) Install JS deps:

```
npm install
```

5) Start the app (two options)

- One command (concurrent server + queue + Vite):

```
composer run dev
```

- Or run separately in two terminals:

```
php artisan serve            # http://127.0.0.1:8000
npm run dev                  # Vite dev server
```

Visit http://127.0.0.1:8000 and open the Market section.

## Environment Notes

- Set `APP_URL` in `.env` if running behind a different host/port.
- For databases other than SQLite, update `DB_*` variables and create the database before `migrate`.

Serve via your web server (Nginx/Apache). Ensure `APP_ENV=production`, `APP_DEBUG=false` and proper cache/queue setup as needed.

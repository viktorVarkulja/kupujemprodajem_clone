# KupujemProdajem Clone (Laravel + Vue + Inertia)

This is a small marketplace prototype inspired by KupujemProdajem. It lets users browse categories, filter listings, and view detailed ads with images. The UI is localized for Serbian where it matters (e.g., condition and delivery labels in the detailed view). Authenticated buyers can start a 1‑on‑1 chat with sellers directly from an ad, and manage conversations on a dedicated “Razgovori” page. Chats show message times in 24‑hour format, differentiate your messages visually, and use toasts for feedback. Owners don’t see the “Pošalji poruku prodavcu” button on their own ads, and guests are prompted to log in before starting a chat.

Out of the box, the seeders create demo users, demo ads (with placeholder images), and several conversations so you can explore the flow immediately.

- Backend: Laravel 12 (PHP 8.2+)
- Frontend: Vue 3 + TypeScript via Inertia + Vite
- Auth: Laravel Fortify
- Styling: Tailwind CSS

## Demo Accounts

After `php artisan migrate --seed`, you can sign in with any of the following users (password for all: `12345678`):

- ana@example.com — Ana Admin
- marko@example.com — Marko Prodavac
- jelena@example.com — Jelena Kupac
- ivan@example.com — Ivan Korisnik
- mila@example.com — Mila Test

Seeders also create several ads for the first few users and attach placeholder images so the homepage and ad pages look realistic. You can start chats from ad pages you do not own, then continue conversations on `/chats`.

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

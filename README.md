# Peshawar Civic GIS Atlas

Laravel 12 + Blade + Alpine + Leaflet civic GIS for Peshawar, seeded with demo facilities and an admin workflow for dataset ingestion, validation, and publishing.

## What Is Implemented

- Public atlas with live facility filters, Leaflet map, facility detail pages, and provenance display
- Admin console with RBAC, dataset catalog, import upload, validation, and publish workflow
- MariaDB-ready GIS schema using lat/lng plus optional geometry columns
- Demo seed data for Peshawar district, categories, metrics, facilities, and operator accounts
- Docs pack under [`docs/`](./docs)

## Stack

- PHP 8.2.12 via XAMPP
- Laravel 12
- MariaDB 10.4 compatible schema
- Blade + Alpine.js
- Leaflet for mapping
- Spatie permissions for RBAC
- PhpSpreadsheet / Laravel Excel dependency stack for CSV/XLSX import support

## Local Setup

1. Ensure XAMPP MySQL is running. On this machine it is currently exposed on port `3307`.
2. Create the database if it does not exist:

```powershell
& 'C:\xampp\mysql\bin\mysql.exe' -u root -P 3307 -e "CREATE DATABASE IF NOT EXISTS gis_project CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

3. Install dependencies and build assets if needed:

```powershell
& 'C:\xampp\php\php.exe' tools\composer.phar install
npm install
npm run build
```

4. Run migrations and seed demo data:

```powershell
& 'C:\xampp\php\php.exe' artisan migrate:fresh --seed
```

5. Start the app:

```powershell
& 'C:\xampp\php\php.exe' artisan serve
```

6. Open `http://127.0.0.1:8000`

## One-Command Dev Run

For local frontend + backend startup on Windows, use:

```powershell
.\run-dev.ps1
```

That starts:

- Laravel backend on `http://127.0.0.1:8000`
- Vite dev server on `http://127.0.0.1:5173`

If you also want the queue worker in the same session:

```powershell
.\run-dev.ps1 -WithQueue
```

## Demo Users

- `admin@civicatlas.test` / `password`
- `analyst@civicatlas.test` / `password`
- `manager@civicatlas.test` / `password`

## Key Paths

- Public atlas: `/`
- Admin dashboard: `/admin`
- Dataset catalog: `/admin/datasets`
- Imports: `/admin/imports`
- Public API: `/api/*`

## Notes

- `.env` and `.env.example` are currently set to `DB_PORT=3307` because the running local MySQL service is still on that port.
- If you later restart XAMPP onto default MySQL port `3306`, update `.env` accordingly.
- The seeded Peshawar boundary is a demo polygon for presentation. Replace it with authoritative boundary geometry before calling the app production-ready.

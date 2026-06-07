# CHTM system audit (Laravel-only)

Audit date: June 2026

## Verdict

The staff application runs entirely on **Laravel 12**: Blade views, form posts, Eloquent, and server-side services. No React, Inertia, or Vite build is required to use the app.

## Runtime stack

| Layer | Technology |
|-------|------------|
| HTTP | `routes/web.php` + `routes/auth.php` |
| UI | Blade, Alpine.js, Tailwind CDN, Tabler icons |
| Data | Eloquent, SQLite/MySQL |
| Sensitive fields | AES-256-GCM (`Aes256GcmEncrypted` cast) |
| Auth | Session guard, `email_hash` login lookup |

## Pages and controllers

| URL | Controller |
|-----|------------|
| `/login` | `AuthenticatedSessionController` |
| `/dashboard` | `DashboardController` |
| `/reservation` | `ReservationController` |
| `/frontoffice` | `FrontOfficeController` |
| `/room` | `RoomController` |
| `/archived` | `ArchivedController` |
| `/audit` | `AuditController` |
| `/settings` | `SettingsController` |
| `/profile` | `ProfileController` |

## Archived frontend (not loaded at runtime)

All legacy React/Inertia/Vite/Supabase files were moved to:

`_archive/unused-frontend/`

| Path | Contents |
|------|----------|
| `spa-react-inertia/` | Former `resources/js/` (React pages and services) |
| `node_modules/` | npm dependencies (gitignored) |
| `config/` | `tsconfig.json`, Tailwind/PostCSS configs |
| `resources-css/` | Former `resources/css/` (Vite Tailwind entry) |
| `package-lock.json` | npm lockfile |

The live app does not reference this folder. **22 PHPUnit tests** pass with the archive in place.

Active UI assets: Blade views, Alpine.js, Tailwind CDN, `public/js/room-calendar.js`.

## Environment

Use `.env.example` as a template. Supabase variables are **not** required. Set `AES_GCM_KEY` in production.

## Checks performed

- No `inertia`, `@vite`, or `react` references under `app/` or `resources/views/`
- `composer.json` has no Inertia/Ziggy packages
- PHPUnit: 22 tests passing

## Local run

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Login: `admin@chtm.local` / `password`

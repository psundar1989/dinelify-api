# Dinelify API

Laravel backend for the Dinelify food ordering platform: REST API for the Flutter mobile app (Sanctum auth) plus a
session-based Blade admin panel, sharing the same models, business rules, and MySQL database.

## Stack

PHP 8.3, Laravel 13, MySQL, Sanctum, spatie/laravel-permission, Blade + Tailwind + Alpine.js (admin panel),
maatwebsite/excel + barryvdh/laravel-dompdf (report exports), Pest.

## Setup

```bash
composer install
cp .env.example .env   # then fill in DB_* and (optionally) SMS_DRIVER/TWILIO_* for production
php artisan key:generate
npm install && npm run build   # admin panel assets (Tailwind/Alpine/Chart.js)
php artisan migrate --seed
php artisan serve
```

Seeded accounts (see `database/seeders/`):

| Role | Login | Password |
|---|---|---|
| Super Admin | `superadmin@dinelify.test` | `Password@123` |
| Admin | `admin@dinelify.test` | `Password@123` |
| Team Lead (North Campus only) | `teamlead@dinelify.test` | `Password@123` |
| Demo customer (mobile app) | mobile `9999999999` | OTP-only — see below |

Admin panel: `http://localhost:8000/admin/login`.

## OTP in non-production environments

`SMS_DRIVER=log` (the default) writes the OTP to `storage/logs/laravel.log` **and** returns it in the
`send-otp` response as `data.debug_otp`, since `APP_ENV != production`. Set `SMS_DRIVER=twilio` and the
`TWILIO_*` env vars for real delivery in production — see `app/Services/Sms/`.

## Business rules

`advance_order_days`, `order_cutoff_time`, and `allow_order_edit` live in the `settings` table (managed from the
admin panel — no redeploy needed to change them) and are enforced exclusively by
`App\Services\OrderRuleService` / `App\Services\OrderService`. The Flutter app never reimplements these rules;
it only renders the `is_orderable` / `cutoff_at` fields the API already computes.

Cutoff semantics: the order for a given `order_date` locks at `order_cutoff_time` on the **previous day**
(e.g. cutoff `20:00` means tomorrow's meals must be finalized by 8pm today).

## API

REST endpoints under `/api/*` — see `routes/api.php`. Every response follows:

```json
{"success": true, "message": "...", "data": {}}
{"success": false, "message": "...", "errors": {}}
```

Auth: Sanctum bearer tokens. Customers get one via `POST /api/auth/verify-otp` or `POST /api/user/register`.
Admins get one via `POST /api/admin/login` (used only for the `/api/reports/*` endpoints — the admin panel
itself uses its own session guard, not these tokens).

## Admin panel

Blade views under `resources/views/admin/`, controllers under `app/Http/Controllers/Admin/`, routes in
`routes/admin.php`. Authorization is enforced server-side via `spatie/laravel-permission` roles
(`super-admin`, `admin`, `team-lead`) and the `admin.permission:<name>` middleware — never a frontend-only
check. Team Leads are scoped to their assigned locations (`location_admin_user` pivot).

## Testing

```bash
./vendor/bin/pest
```

`tests/Feature/OrderRulesTest.php` and `tests/Feature/RegistrationFlowTest.php` cover the order business rules
(advance window, cutoff lock, duplicate orders, unavailable meals/dates) and the OTP → registration flow.

## Environments

`.env` drives `APP_ENV` (local/qa/production). No secrets are ever shipped to the Flutter app — it only ever
receives a base API URL per build flavor (see `../dinelify_app/README.md`).

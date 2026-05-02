# hPanel Subdomain Deployment (Laravel)

This folder prepares your Laravel project for easy deployment to a Hostinger hPanel subdomain.

## Target Structure on Hosting

Use this structure on your hosting account:

- `domains/your-subdomain/public_html` (web root)
- `domains/your-subdomain/laravel_app` (Laravel app files outside web root)

## What This Kit Provides

- `templates/public_html/index.php`: preconfigured entrypoint for `../laravel_app`
- `templates/public_html/.htaccess`: Laravel rewrite rules
- `scripts/prepare-hpanel-deploy.ps1`: creates ready upload package

## 1) Build Deployment Package Locally

From project root, run:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\prepare-hpanel-deploy.ps1
```

It creates:

- `deploy/hpanel/package/laravel_app`
- `deploy/hpanel/package/public_html`

## 2) Upload in hPanel File Manager

For your subdomain:

1. Upload all files from `deploy/hpanel/package/public_html` into subdomain `public_html`.
2. Upload all files from `deploy/hpanel/package/laravel_app` into sibling folder `laravel_app`.

Result should be:

- `.../public_html/index.php`
- `.../laravel_app/artisan`
- `.../laravel_app/bootstrap/app.php`
- etc.

## 3) Environment + Permissions

Inside `laravel_app`:

1. Copy `.env.example` to `.env`.
2. Set DB and APP values (`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-subdomain`).
3. Ensure writable directories:
   - `storage`
   - `bootstrap/cache`

## 4) Run Laravel Commands (hPanel Terminal or SSH)

From `laravel_app`:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If your host does not allow CLI, run these locally and upload generated assets/caches carefully.

## 5) Frontend Assets

Build locally before packaging:

```bash
npm ci
npm run build
```

The generated `public/build` folder is copied into `public_html/build`.

## Notes

- Do **not** upload local `.env`.
- Do **not** upload `.git`, `node_modules`, or local logs.
- If you have `xirfadkaablogo.jpg` in the project root, the packaging script copies it into `public_html/xirfadkaablogo.jpg` (used by the UI/reports).
- If your subdomain path differs, update `public_html/index.php` relative paths.

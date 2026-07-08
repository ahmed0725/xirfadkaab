# Xirfad Kaab — System Manual

This manual is for **system administrators**, **hosting operators**, and **technical staff** who install, configure, secure, and maintain the application.

---

## 1. Purpose

**Xirfad Kaab** is a web application for managing a training or school-style organization: **students**, **classes** (including schedules and course types), **subjects**, **attendance**, **fees**, **exams**, **expenses**, **inventory**, and **reports**. It is built with **Laravel** (PHP) and a server-rendered UI (Blade templates).

---

## 2. Technical requirements

| Component | Version / notes |
|-----------|-----------------|
| PHP | 8.2+ (extensions typically required: `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`) |
| Composer | For installing PHP dependencies |
| Database | MySQL / MariaDB recommended for production; SQLite is acceptable for local testing |
| Node.js + npm | Required **on the build machine** to compile frontend assets (`npm run build`) |

---

## 3. Architecture overview

- **Backend:** Laravel 12 application code lives in the project root (or in `laravel_app` when deployed using the hPanel package layout).
- **Frontend:** Tailwind/CSS and JavaScript are built with Vite; compiled assets are served from `public/build` (and copied to `public_html/build` in the Hostinger package).
- **Auth:** Session-based login (Breeze). Email verification can be enabled per Laravel configuration.
- **Authorization:** A `role` field on the `users` table (`admin`, `user`, `teacher`) plus Laravel **policies** (e.g. `SchoolClassPolicy`) for class visibility.

**Hostinger hPanel layout (typical):**

- `public_html` — web root (`index.php` points to the Laravel `public` folder’s logic via the provided template).
- `laravel_app` — full Laravel project **outside** the web root (safer; `storage` and `.env` are not directly web-accessible).

See [deploy/hpanel/README.md](../deploy/hpanel/README.md) for packaging and upload steps.

---

## 4. Installation (summary)

### 4.1 Local or full server install

1. Clone the repository and run `composer install`.
2. Copy `.env.example` to `.env`, set `APP_KEY` (`php artisan key:generate`), database credentials, and `APP_URL`.
3. Run `php artisan migrate` (and optionally `php artisan db:seed` for sample data in non-production).
4. Build assets: `npm ci` and `npm run build`.
5. Point the web server document root to the `public` directory (or use the hPanel `index.php` bridge).

### 4.2 hPanel package (file upload)

1. From the project root, run `scripts/prepare-hpanel-deploy.ps1` to generate `deploy/hpanel/package/public_html` and `deploy/hpanel/package/laravel_app`.
2. Upload those folders to the hosting account as described in the deploy README.
3. On the server, in `laravel_app`: copy `.env.example` to `.env`, configure, then run Composer and Artisan commands (see section 5).

---

## 5. Environment configuration (`.env`)

Critical variables (non-exhaustive):

| Variable | Purpose |
|----------|---------|
| `APP_NAME` | Application name (shown in the UI in places). |
| `APP_ENV` | `local` / `production` — use `production` on live sites. |
| `APP_DEBUG` | Must be `false` in production. |
| `APP_URL` | Full base URL, e.g. `https://subdomain.example.com` (affects URL generation and trust). |
| `DB_*` | Database connection. |
| `SESSION_*` / `FILESYSTEM_*` | Session and file storage; default file session is typical. |

**Never commit** a real `.env` file to Git. Use `.env.example` as a template only.

---

## 6. Post-deploy commands (production)

Run from the `laravel_app` directory (use the host’s PHP CLI if versioned, e.g. `php82`):

```bash
composer install --no-dev --optimize-autoloader --no-interaction
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

After code updates, run at least:

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If frontend assets change, rebuild locally and redeploy `public/build` (or the packaged `public_html/build`).

---

## 7. User roles (authorization model)

| Role | Typical use |
|------|-------------|
| **admin** | Full access: user management, **Course types**, **System settings**, recording/editing **fees** and **additional fees**, **expenses** and **inventory** CRUD, **reports**, destructive deletes (students/classes/subjects/fees/attendance records where applicable). |
| **user** | Office/staff: manage **students** (no student delete), **classes** and **subjects** (no delete on those resources unless admin — see routes), view **fees**, **expenses**, **inventory**, create/manage **exams**; cannot manage course types, users, settings, or most financial write operations reserved for admin. |
| **teacher** | **View** assigned **classes** (list + detail), **exams** (list, detail, enter results), **attendance** (list, mark, edit own-class records; cannot delete attendance). Teachers only see **classes they are assigned to** and can only mark attendance for those classes. |

Exact route permissions are defined in `routes/web.php` and in policies such as `app/Policies/SchoolClassPolicy.php`.

---

## 8. Core data model (high level)

- **Users** — login accounts; `role` determines menus and actions.
- **Course types** — reusable categories (e.g. skill areas). Managed only by **admin**. Classes reference a course type.
- **School classes (`school_classes`)** — a cohort offering: name, course type, start date, duration (months), computed end date, class time, shift, optional classroom, monthly fee default, active flag. Teachers are linked via pivot table **`school_class_user`**.
- **Students** — each belongs to one school class (`school_class_id`).
- **Subjects** — belong to one school class; used for attendance and exams when subject-specific records are needed.
- **Attendance** — keyed by student, date, optional subject; includes status (present/absent/late). Unique constraint prevents duplicate rows per student/date/subject combination.

Parallel cohorts may share the same **class name**; the UI distinguishes them using a **display label** (name, course type, time, shift).

---

## 9. Filesystem permissions

On Linux hosting, the web server user must be able to write:

- `storage/` (logs, cache, compiled views, uploads if used)
- `bootstrap/cache/`

Typical pattern: directories `775` or `775` with correct ownership (e.g. `www-data` or the panel user).

---

## 10. Backups

Recommended regular backups:

1. **Database** — full dump (mysqldump or hosting backup tool).
2. **`storage/app`** — private uploads if used.
3. **`.env`** — store securely offline (not in Git); needed to restore environment-specific secrets.

---

## 11. Troubleshooting

| Symptom | Checks |
|---------|--------|
| HTTP 500 | `storage/logs/laravel.log`; verify `APP_DEBUG` temporarily in staging only; permissions on `storage` / `bootstrap/cache`. |
| Login works but pages 403 | User `role` missing or wrong; middleware `role:` on routes. |
| Classes empty for teacher | Assign teachers on the **class edit** screen (multi-select); pivot `school_class_user`. |
| Migration errors | Run `php artisan migrate --force`; ensure DB user has DDL rights; never edit production DB manually without backup. |
| Styles/scripts missing | Run `npm run build` and deploy `public/build`; clear browser cache. |

---

## 12. Security checklist

- `APP_DEBUG=false` and `APP_ENV=production` on live sites.
- HTTPS (`APP_URL` with `https://`).
- Strong passwords for admin accounts.
- Keep Laravel and Composer dependencies updated (`composer update` in a controlled process).
- Restrict SSH/FTP access; use Git deploy keys or tokens without storing passwords in plain text.

---

## 13. Related documents

- [USER_GUIDE.md](USER_GUIDE.md) — day-to-day usage for staff and teachers.
- [deploy/hpanel/README.md](../deploy/hpanel/README.md) — Hostinger packaging and folder layout.

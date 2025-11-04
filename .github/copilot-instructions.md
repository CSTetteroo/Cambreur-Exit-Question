# Copilot instructions for this repo

## Big picture
- This is a Laravel 10 app with Breeze auth, Vite, Tailwind, and Alpine. The domain is a small Q&A system for classes with three roles: admin, docent (teacher), student.
- Core entities and relations:
  - `User` has `role` in ['admin','docent','student'] and many-to-many `classes()` via `class_user` pivot.
  - `ClassModel` (named to avoid PHP reserved word) maps to `classes` and may have an `active_question_id`.
  - `Question` belongs to `creator` (a `User`), has many `choices` and `answers`.
  - `Answer` belongs to `question`, `user`, optional `choice` for MC; `Choice` belongs to `question`.
  - See models under `app/Models/*.php` and migrations in `database/migrations/*` for schema.

## Routing and middleware
- Web routes are in `routes/web.php`. Auth scaffolding routes are in `routes/auth.php` (Breeze).
- Admin-only UI is grouped under `Route::middleware(['auth','verified','admin'])` with alias `admin` wired in `app/Http/Kernel.php` to `App\Http\Middleware\IsAdmin`.
- Dashboards:
  - Admin: `GET /admin_dashboard` -> `UserController@admin_index` renders `resources/views/admin_dashboard.blade.php`.
  - Logged-in users: `GET /dashboard` renders `resources/views/user_dashboard.blade.php` and redirects admins to the admin dashboard.

## Controllers and patterns
- `UserController` handles basic CRUD and role/class assignment. Validation differs by role (students must have at least one class). It uses `classes()->sync([...])` for many-to-many updates.
- `ClassController@store` creates new classes with `class_name` input.
- Use named routes when adding links in blades: `users.store`, `users.edit`, `users.update`, `users.destroy`, `classes.store`.

## Views and frontend
- Blade components/layout come from Breeze (`<x-app-layout>`). Styling is Tailwind 3 with a dark theme.
- Assets are built with Vite; entry at `resources/js/app.js` and `resources/css/app.css`. Tailwind config in `tailwind.config.js`.

## Developer workflows
- Install and serve:
  - PHP deps: `composer install`; copy `.env` and set DB; run `php artisan key:generate`.
  - Frontend: `npm install`; dev server `npm run dev`; prod build `npm run build`.
  - DB: `php artisan migrate --seed` (seeds `admin@example.com` / password `admin123`).
  - Run: `php artisan serve` (or via your local web stack). Log in, then visit `/admin_dashboard`.
- Tests: `php artisan test` (PHPUnit 10; config in `phpunit.xml`).

## Conventions and gotchas
- Use `ClassModel` for Eloquent model; table is `classes`. Pivot is `class_user` with unique composite (`class_id`, `user_id`).
- Roles are simple strings; gate/authorization currently enforced via middleware and route groups, not policies.
- Keep validation in controllers consistent with current forms (see `admin_dashboard.blade.php` and `edit_user.blade.php`).
- Prefer Eloquent relationships already defined (e.g., `Question::latest()->take(50)` on dashboards) and avoid N+1s by eager loading when extending features.

## Typical additions
- When adding question CRUD for docenten: create a controller under `app/Http/Controllers`, add resource routes in `routes/web.php` within `auth` (and role check), and use `Question::$fillable` fields (`content`, `type`, `created_by`).
- If you add policies or gates later, register in `app/Providers/AuthServiceProvider.php` and refactor route middleware accordingly.

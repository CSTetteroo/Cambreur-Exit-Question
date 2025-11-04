# Copilot instructions for this repo

## Big picture
- Laravel 10 app with Breeze auth, Vite, Tailwind, and Alpine. Domain: small class Q&A with roles: admin, docent (teacher), student.
- Data model (see `app/Models/*.php`):
  - `User` has string `role` and many-to-many `classes()` via `class_user` pivot.
  - `ClassModel` (not `Class`) maps to `classes`, optional `active_question_id` → `activeQuestion()`.
  - `Question` (`$fillable`: content, type, created_by) belongs to `creator` (`User`); has many `choices` and `answers`.
  - `Answer` (`$fillable`: question_id, user_id, choice_id, answer_text, is_correct) belongs to `question`, `user`, optional `choice`.
  - `Choice` (`$fillable`: question_id, label, text, is_correct) belongs to `question`.

## Routing and middleware
- Web: `routes/web.php`. Breeze auth routes: `routes/auth.php`.
- Middleware aliases in `app/Http/Kernel.php`: `admin` → `App\Http\Middleware\IsAdmin`, `docent` → `IsDocent`, plus `auth`/`verified`.
- Dashboards:
  - Admin: `GET /admin_dashboard` → `UserController@admin_index` → `resources/views/admin_dashboard.blade.php`.
  - Users: `GET /dashboard` → `resources/views/user_dashboard.blade.php`. Admins are redirected to admin dashboard. Students see active class questions; docenten see latest own questions. Avoid N+1 by eager loading `creator`, `choices` as used.
- Docent question management (prefix `docent.`, see `QuestionController`):
  - `docent.questions.index|store|activate|results|setCorrect|grade|destroy`, and `docent.classes.clear`.
- Student answers: `answers.store` (`AnswerController@store`).

## Controllers and patterns
- `UserController`: CRUD + class assignment; students must have ≥1 class; uses `$user->classes()->sync([...])`.
- `ClassController@store`: creates classes from `class_name` input.
- FKs and constraints enforced in migrations; rely on Eloquent relationships where possible.

## Conventions and gotchas
- Always use `ClassModel` for the `classes` table (PHP reserved word). Pivot table is `class_user` with unique composite (`class_id`,`user_id`).
- Migrations:
  - `2025_11_04_121000_unique_answer_per_user_per_question` enforces unique (`question_id`,`user_id`) in `answers`.
  - `2025_11_04_120000_restore_user_fk_on_class_user` restores `class_user.user_id → users.id` with cascade on delete.
- Roles are plain strings; authorization is via route middleware, not policies.
- Blade uses Breeze components (`<x-app-layout>`). Tailwind config in `tailwind.config.js`. Vite entry: `resources/js/app.js`, `resources/css/app.css`.

## Developer workflows
- Install: `composer install`; copy `.env`, set DB; `php artisan key:generate`.
- Frontend: `npm install`; dev `npm run dev`; build `npm run build`.
- DB: `php artisan migrate --seed` (seeds `admin@example.com` / `admin123`).
- Run: `php artisan serve` (or local stack like Laragon); log in → `/admin_dashboard`.
- Tests: `php artisan test` (PHPUnit 10).

## File map examples
- Models: `app/Models/{User,ClassModel,Question,Answer,Choice}.php`.
- Routes: `routes/web.php` (see docent routes), `routes/auth.php`.
- Views: `resources/views/{admin_dashboard,user_dashboard,docent_questions}.blade.php`.

If anything above is unclear or missing (e.g., grading rules, result views), tell me what you’d like to automate next and I’ll refine these rules.

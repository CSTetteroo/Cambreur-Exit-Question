# Copilot instructions for this repository (Concise)

## Big Picture
- Laravel 10 monolith for small class Q&A with three roles: `admin`, `docent` (teacher), `student`.
- Frontend: Vite + Tailwind + Alpine; Auth/UI scaffolding via Laravel Breeze.
- Main features: create/manage questions (`Question`), multiple-choice `Choice`s, store student `Answer`s, per-`ClassModel` active question tracking.

## Key models & locations
- `app/Models/User.php` — has `role` (string) and `classes()` many-to-many via `class_user` pivot.
- `app/Models/ClassModel.php` — maps to `classes` (use this name; never create `Class`). Has optional `active_question_id` and `activeQuestion()` relation.
- `app/Models/Question.php` — `content`, `type` (`open`|`multiple_choice`), `created_by`; relations: `creator`, `choices`, `answers`.
- `app/Models/Choice.php`, `app/Models/Answer.php` — see fields and `$fillable` in those files.

## Routes, controllers & middleware
- Web routes: `routes/web.php` (auth routes in `routes/auth.php`). Many docent routes are prefixed `docent.`
- Important controllers: `QuestionController`, `AnswerController`, `UserController`, `ClassController` (check `app/Http/Controllers`).
- Middleware aliases in `app/Http/Kernel.php`: `admin` → `IsAdmin`, `docent` → `IsDocent`. Authorization is role/middleware based (not policies).

## Views & Blade patterns
- Uses Breeze components like `<x-app-layout>`. Example: `resources/views/docent_questions.blade.php` implements dynamic UI with Alpine (`x-data`, `x-show`) and server-side rendering for lists.
- Avoid N+1 queries in list views: the code commonly expects eager loaded `creator`, `choices`, and `answers_count` / `choices_count` (see controller index methods).

## Migrations & gotchas to watch for
- Use `ClassModel` (not `Class`) when referring to the `classes` table and `class_user` pivot (unique composite `class_id,user_id`).
- There are deliberate migration constraints:
  - `2025_11_04_121000_unique_answer_per_user_per_question` — unique (`question_id`,`user_id`) on `answers` means duplicate answer inserts will fail with DB error.
  - `2025_11_04_120000_restore_user_fk_on_class_user` — the pivot `user_id` FK cascades on delete; migrations must be applied in order.

## Developer workflows (commands & notes)
- Backend install (PowerShell):
  - `composer install`;
  - copy `.env.example` → `.env`, set DB credentials;
  - `php artisan key:generate`;
  - `php artisan migrate --seed` (seeder creates `admin@example.com` / `admin123`).
- Frontend (PowerShell):
  - `npm install`;
  - dev: `npm run dev` (Vite) — use separate terminal; on Windows use PowerShell commands as-is;
  - build: `npm run build`.
- Run app:
  - `php artisan serve` (or use Laragon/XAMPP). App homepage for logged-in users: `/dashboard`; admin sees `/admin_dashboard`.

## Testing & debugging
- Tests: `php artisan test` (PHPUnit 10). Run specific tests with `php artisan test --filter=TestName`.
- When adding DB-affecting tests, remember unique constraints on `answers` — create fresh DB or use transactions when seeding.

## Conventions & quick patterns
- Naming: use `ClassModel` for the model representing `classes` table.
- Role checks: route middleware, not policies — add middleware to routes if gating by role.
- When adding list endpoints used in views, eager load `creator`, `choices` and include counts (use `withCount(['choices','answers'])`) to match blade expectations.
- Route names used by views: e.g., `route('docent.questions.results', $q)`, `route('docent.questions.destroy', $q)`, `route('docent.questions.store')` — reuse these names.

## Where to change behavior
- UI and per-teacher controls: `resources/views/docent_questions.blade.php` (Alpine interop example).
- Activation of questions for classes: `QuestionController@activate` and `docent.classes.clear` routes — check `routes/web.php` for exact route names.

## Integrations & external deps
- Laravel Breeze (auth), Vite (dev server), Tailwind (styling), Alpine (small UI behavior). No external APIs detected.

If anything here is incomplete or you'd like examples (controller index & eager-loading patterns, or a sample migration change), tell me which area to expand and I will update this file.

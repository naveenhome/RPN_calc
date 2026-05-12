# Repository Guidelines

## Project Structure & Module Organization
This repository is a monorepo for the RPN Calculator. `frontend/` contains the Angular SPA; keep shared client concerns in `src/app/core/` and user-facing slices in `src/app/features/`. `backend/` contains the Laravel API; business rules live in `app/Domain/`, use-case orchestration in `app/Application/`, and HTTP entrypoints in `app/Http/`. Database schema changes belong in `backend/database/migrations/`. Product scope and acceptance criteria are documented in `PRD_RPN_Calculator.md`.

## Build, Test, and Development Commands
Use the root `Makefile` for common workflows:

- `make install` installs frontend and backend dependencies.
- `docker compose up -d mysql` starts the local MySQL service.
- `make frontend` runs `ng serve`.
- `make backend` runs `php artisan serve`.
- `make backend-migrate` applies Laravel migrations.
- `make test` runs Angular and Laravel test suites.
- `cd frontend && npm run test:coverage` generates frontend coverage output.

## Coding Style & Naming Conventions
Use 2 spaces in Angular/TypeScript and 4 spaces in PHP. Follow Angular standalone-component conventions and kebab-case file names such as `calculator-page.component.ts`. In Laravel, follow PSR-12: PascalCase classes, camelCase methods, snake_case columns. Keep controllers and requests thin. Put calculation behavior in domain or application classes, not in routes or views. Prefer small, single-purpose classes and explicit dependencies via injection.

## Testing Guidelines
Work test-first for all behavior changes. Backend domain rules belong in `backend/tests/Unit/`; API and persistence flows belong in `backend/tests/Feature/Api/`. Frontend tests live beside the code as `*.spec.ts`. Cover success paths and edge cases from the PRD, especially stack underflow, unknown tokens, division by zero, factorial validation, and history ordering. Laravel tests use in-memory SQLite; local development uses MySQL.

## Commit & Pull Request Guidelines
Use focused, imperative commits such as `feat: add calculation history endpoint` or `test: cover factorial validation`. PRs should include a short summary, linked requirement or issue, test evidence, and screenshots for UI changes. Call out migration, API contract, or environment changes explicitly.

## Configuration & Safety
Do not commit secrets or `backend/.env`. Treat migrations as append-only after commit. Keep API error responses consistent and preserve the current application/domain separation when adding features.

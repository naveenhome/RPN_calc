# RPN Calculator

Monorepo for the RPN Calculator web application defined in `PRD_RPN_Calculator.md`.

## Architecture
- `frontend/`: Angular SPA with a feature-oriented structure (`core/`, `features/`).
- `backend/`: Laravel API with application and domain layers around the RPN engine.
- `docker-compose.yml`: Local MySQL service for development.

The backend keeps evaluation logic in the domain layer and isolates HTTP concerns in controllers and requests. The frontend keeps UI in feature folders and API access in `core/services`.

## Development
1. `make install`
2. `docker compose up -d mysql`
3. `make backend-migrate`
4. `make test`

## Testing Strategy
- Backend unit tests cover the evaluator and edge cases.
- Backend feature tests cover API contracts and persistence.
- Frontend unit tests cover the application shell and calculator page behavior.
- Local tests use SQLite in memory for Laravel; development uses MySQL.

## Clean Code Guardrails
- Keep controllers thin and move business rules into services.
- Prefer constructor or framework injection over static calls in domain code.
- Add tests before behavior changes and keep behavior-focused test names.
- Use small, single-purpose classes; the evaluator uses a strategy-style operator set.

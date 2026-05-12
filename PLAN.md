# RPN Calculator Delivery Plan

## Overview

This plan is derived from [PRD_RPN_Calculator.md](/Users/naveenkumarsingh/projects/learning/RPN_calc/PRD_RPN_Calculator.md) and organizes delivery into `Now`, `Next`, and `Later` horizons. The priority is to ship all P0 requirements by **2026-05-31**.

## Goal

Deliver a production-ready web-based RPN calculator that:

- evaluates RPN expressions correctly
- persists successful and failed calculations
- exposes a clean API for calculation and history
- provides a simple Angular UI for input, result viewing, and history review

## Planning Assumptions

- `Now` maps to PRD P0 and must be completed for v1 launch.
- `Next` maps to PRD P1 and should begin after v1 is stable.
- `Later` maps to PRD P2 and represents post-v1 expansion.
- Open product questions in the PRD are unresolved unless explicitly decided.
- The current target launch date is **2026-05-31**.

## Now

These epics represent the must-have work needed for v1 release.

### Epic 1: Core RPN Evaluation Engine

Build the backend evaluation service for space-delimited RPN expressions with full P0 operator and error support.

Acceptance criteria:

- The evaluator accepts expressions such as `3 4 +` and returns the correct result.
- Binary operators `+`, `-`, `*`, `/`, and `^` are supported for integer and floating-point operands.
- Unary operators `%` and `!` are supported.
- Floating-point calculations follow IEEE 754 double-precision behavior.
- `0 !` returns `1`, `5 !` returns `120`, `75 %` returns `0.75`, and `2 10 ^` returns `1024`.
- Descriptive errors are returned for stack underflow, division by zero, negative factorial, non-integer factorial, unknown tokens, and incomplete expressions with too many operands.
- Evaluation logic lives in a dedicated backend service class and is covered by unit tests.

### Epic 2: Calculation Persistence and History Model

Persist every evaluation outcome so the calculator supports auditability and history replay.

Acceptance criteria:

- Every successful calculation stores expression, result, status, and evaluation timestamp in MySQL.
- Every failed calculation stores expression, error message, status, and evaluation timestamp in MySQL.
- Success and failure rows are clearly distinguishable in the schema.
- History records are returned newest first.
- Bulk clear removes all stored history rows.
- Database migrations and schema support the PRD data model for `calculations`.

### Epic 3: Public Calculation API

Expose the backend capability through a small, consistent REST API.

Acceptance criteria:

- `POST /api/calculate` accepts `{ "expression": "3 4 +" }`.
- Successful evaluation returns HTTP 200 with `id`, `expression`, and `result`.
- Evaluation errors return HTTP 422 with a consistent, human-readable error payload.
- `GET /api/history` returns paginated history with `data`, `total`, `per_page`, and `current_page`.
- `DELETE /api/history` returns HTTP 204 and leaves history empty on subsequent fetch.
- Feature tests cover success flows, validation, persistence, ordering, and clear-history behavior.

### Epic 4: Angular Calculator Experience

Deliver the initial single-page frontend for entering expressions and reviewing outcomes.

Acceptance criteria:

- The page includes an expression input, submit control, result area, and history list.
- These primary controls are visible without scrolling on a `1280x720` viewport.
- Users can submit expressions with the Enter key or by clicking the submit button.
- The UI displays either the result or a descriptive error after each evaluation.
- The history list renders persisted entries in newest-first order.
- The history list updates immediately after each successful or failed evaluation.
- A clear-history action removes persisted history and updates the UI accordingly.

### Epic 5: Release Readiness and Quality Gate

Prepare the product for v1 release with enough quality coverage to meet the PRD goals.

Acceptance criteria:

- All P0 requirements in the PRD are covered by backend and frontend tests.
- Error responses remain human-readable and do not expose stack traces.
- End-to-end validation confirms calculation, error handling, persistence, history retrieval, and history clearing.
- The backend meets the PRD performance target of `<= 200ms p95` for `POST /api/calculate` in staging or equivalent verification.
- No blocking correctness issues remain in supported operator and edge-case scenarios.

## Next

These epics strengthen usability after the v1 foundation is complete.

### Epic 6: Faster Repeat Calculations

Acceptance criteria:

- Clicking a history entry repopulates the expression input.
- Users can edit and resubmit recalled expressions.
- Re-running a historical expression follows the same validation and persistence flow as a fresh entry.

### Epic 7: Guidance and Input Confidence

Acceptance criteria:

- A collapsible help panel documents supported operators and example expressions.
- The UI provides lightweight structural feedback before submit.
- Pre-submit guidance improves clarity without blocking valid expressions.

### Epic 8: Convenience and Granular History Management

Acceptance criteria:

- Users can copy the latest result with one click.
- Users can delete an individual history entry without clearing the full list.
- Individual deletion preserves correct ordering and UI consistency.

### Epic 9: Mobile-Ready Responsive Layout

Acceptance criteria:

- The calculator remains functional and readable down to `375px` viewport width.
- Input, result, and history workflows remain usable on small screens.
- The responsive layout does not break core calculation actions.

## Later

These epics represent post-v1 product expansion.

### Epic 10: Personalization and Scoped History

Acceptance criteria:

- Users can authenticate and access personal calculation history.
- History becomes user-scoped rather than globally shared.
- Existing calculation behavior remains unchanged when account features are introduced.

### Epic 11: Shareable Calculation Workflows

Acceptance criteria:

- A URL can pre-populate the calculator with an expression.
- Shared links reproduce the intended starting input reliably.
- The share flow works without adding unnecessary friction to the core calculator experience.

### Epic 12: Scientific and Precision Expansion

Acceptance criteria:

- Additional scientific operators such as `log`, `ln`, `sqrt`, `sin`, `cos`, and `tan` are supported.
- Precision expectations and validation rules are documented and test-covered.
- An opt-in arbitrary-precision mode can be introduced without destabilizing the default evaluator.

### Epic 13: History Enrichment and Platform Hardening

Acceptance criteria:

- Users can label or annotate history entries.
- API protections such as rate limiting are in place for public usage.
- History and calculation endpoints remain reliable as usage scales.

## Milestones

- **2026-05-10**: database schema and migration direction finalized
- **2026-05-14**: evaluator service and unit tests complete
- **2026-05-18**: calculation and history APIs complete
- **2026-05-24**: Angular v1 UI integrated with backend
- **2026-05-28**: end-to-end testing and bug fixing complete
- **2026-05-31**: production-ready v1 launch

## Key Decisions To Resolve Early

- Decide whether history is global or session-scoped before database schema is finalized.
- Decide whether error calculations should be shown in the UI history, or only stored for audit.
- Confirm the supported factorial upper bound for v1, with `20!` as the current PRD default.

## Definition of Done for v1

v1 is complete when all `Now` epics are finished, all P0 acceptance criteria from the PRD pass, and the product is ready to release by **2026-05-31**.

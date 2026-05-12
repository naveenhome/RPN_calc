# BDD Feature Specifications

These specifications reorganize PLAN epics 1-8 into vertical, customer-centric
features. Each feature describes behavior the user can observe, while unit and
API tests continue to cover implementation details such as the evaluator,
database schema, and HTTP contracts.

## Vertical Feature Map

| BDD feature | Source epics | Customer outcome |
| --- | --- | --- |
| `calculation/basic-arithmetic.feature` | Epics 1, 2, 3, 4, 5 | User evaluates a basic RPN expression and sees it saved in history. |
| `calculation/advanced-operators.feature` | Epics 1, 2, 3, 4, 5 | User evaluates powers, percentages, factorials, and floating-point expressions. |
| `calculation/invalid-expression-feedback.feature` | Epics 1, 2, 3, 4, 5 | User receives clear feedback for malformed expressions and failed attempts are auditable. |
| `history/calculation-history.feature` | Epics 2, 3, 4, 5 | User reviews prior calculations newest first. |
| `history/clear-history.feature` | Epics 2, 3, 4, 5 | User clears all stored calculation history. |
| `history/reuse-history-entry.feature` | Epic 6 | User recalls a prior expression, edits it, and submits it again. |
| `guidance/operator-help.feature` | Epic 7 | User can discover supported operators and examples. |
| `guidance/input-confidence.feature` | Epic 7 | User gets lightweight pre-submit expression feedback. |
| `convenience/copy-result.feature` | Epic 8 | User copies the latest result with one action. |
| `convenience/delete-history-entry.feature` | Epic 8 | User removes one history item without clearing everything. |

## Automation Notes

- Keep feature files in customer language.
- Avoid mentioning Angular, Laravel, controllers, database tables, or service class names in scenarios.
- Tag P1 feature files with `@p1` so the P0 release acceptance path can run independently.
- When a data table drives history ordering, state the ordering in the step text, such as `from oldest to newest`.
- Verify implementation details through unit tests and API feature tests.
- When automated, prefer end-to-end scenarios for the main happy paths and keep exhaustive edge cases in lower-level tests.

## Running Behat

- `make bdd` runs the P0 Behat suite through the Laravel backend.
- `cd backend && composer bdd` runs the same P0 suite directly.
- `cd backend && composer bdd:p1` targets `@p1` scenarios once those frontend-oriented behaviors have implementation-backed step definitions.

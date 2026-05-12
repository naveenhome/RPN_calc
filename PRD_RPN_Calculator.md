# Product Requirements Document
## RPN (Reverse Polish Notation) Calculator — Web Application

**Version:** 1.0  
**Date:** 2026-05-03  
**Target delivery:** 2026-05-31  
**Stack:** Angular · Laravel · MySQL  

---

## 1. Problem Statement

Engineers and scientists frequently work with complex mathematical expressions where operator precedence ambiguity can lead to errors. Traditional infix calculators require parentheses and impose a cognitive overhead in managing precedence rules. Reverse Polish Notation (postfix) eliminates this ambiguity entirely — every expression has exactly one unambiguous evaluation order — making it the preferred input style in many scientific, engineering, and financial contexts (HP calculators, stack-based computation models, Forth programming).

There is currently no dedicated web-based RPN calculator that combines a modern interface with server-side persistence of calculation history, depriving engineering teams of a shareable, auditable tool for computational workflows.

---

## 2. Goals

1. **Correctness**: Evaluate all RPN expressions — including binary arithmetic, exponentiation, percentage, and factorial — with full IEEE 754 floating-point accuracy, producing zero silent errors on well-formed inputs.
2. **Auditability**: Persist every calculation (expression + result + timestamp) to the database so users can review and reproduce prior work.
3. **Usability**: A first-time user familiar with RPN notation should be able to enter and evaluate an expression within 30 seconds of opening the app, with no tutorial required.
4. **Reliability**: The backend API should handle malformed or edge-case expressions (division by zero, factorial of non-integer, overflow) with clear, descriptive error messages rather than silent failures.
5. **Delivery**: Ship a production-ready v1 by 2026-05-31 with all P0 requirements met.

---

## 3. Non-Goals

- **User authentication / accounts**: All sessions are anonymous. Personalised history, user profiles, and multi-user isolation are explicitly out of scope for v1. (Rationale: adds significant backend complexity; the primary use case is single-user or shared-terminal contexts.)
- **Voice or natural-language input**: Only keyboard text input is supported. (Rationale: NLP parsing of mathematical expressions is a separate, complex initiative.)
- **Mobile-native apps**: The product is a responsive web app only; iOS/Android packaging is not in scope. (Rationale: separate distribution and build pipeline effort.)
- **Graphing / plotting**: The calculator does not render graphs or visualisations of functions. (Rationale: out of scope for a computation-focused v1; a separate graphing module could be added in v2.)
- **Arbitrary-precision arithmetic**: Results are computed using standard 64-bit floating-point. Arbitrary-precision libraries (e.g., bcmath beyond Laravel's defaults) are not required for v1. (Rationale: sufficient for the target audience's daily use.)

---

## 4. User Stories

### Primary persona — engineer / scientist

- As an engineer, I want to type an RPN expression (e.g., `3 4 + 2 *`) and immediately see the evaluated result so that I can verify computations without managing parentheses.
- As a scientist, I want to use floating-point operands (e.g., `3.14 2.0 *`) so that my calculations reflect real-world measurements.
- As a user, I want to use `^` for exponentiation (e.g., `2 10 ^`) so that I can compute powers without a separate function syntax.
- As a user, I want to use `%` for percentage conversion (e.g., `75 %`) so that I can quickly convert percentages without knowing the formula.
- As a user, I want to use `!` for factorial (e.g., `7 !`) so that I can compute combinatorial values inline.
- As a user, I want to see a clear error message when I enter a malformed expression (e.g., `+ 3`) so that I understand what went wrong and can correct my input.
- As a user, I want my calculation history persisted across page reloads so that I can reference prior results without retyping them.
- As a user, I want to clear my calculation history so that I can start fresh without old entries cluttering my view.

### Edge-case stories

- As a user, I want to enter very large factorials (e.g., `20 !`) and receive an accurate integer result so that I can use the calculator for combinatorics.
- As a user, I want to attempt `0 !` and receive `1` (mathematically correct) so that edge cases do not require workarounds.
- As a user, I want to attempt division by zero (e.g., `5 0 /`) and receive an explicit error rather than `Infinity` or `NaN` so that I am not misled by a silent bad result.
- As a user, I want to attempt factorial on a negative number (e.g., `-3 !`) and receive a descriptive error so that I understand the operation is undefined.

---

## 5. Requirements

### 5.1 Must-Have — P0

#### Expression parsing and evaluation

| # | Requirement | Acceptance criteria |
|---|---|---|
| P0-01 | Accept a space-delimited RPN expression as keyboard text input | Given input `3 4 +`, when submitted, then result `7` is displayed |
| P0-02 | Support binary operators: `+` (add), `-` (subtract), `*` (multiply), `/` (divide) | Each operator produces the mathematically correct result for integer and floating-point operands |
| P0-03 | Support floating-point operands | `3.14 2 *` evaluates to `6.28`; precision follows IEEE 754 double |
| P0-04 | Support `^` for exponentiation | `2 10 ^` evaluates to `1024`; `2.0 0.5 ^` evaluates to `~1.4142` |
| P0-05 | Support `%` as unary percentage | `75 %` evaluates to `0.75`; `200 %` evaluates to `2.0` |
| P0-06 | Support `!` as unary factorial | `5 !` evaluates to `120`; `0 !` evaluates to `1` |
| P0-07 | Return a descriptive error for stack underflow (too few operands for an operator) | `+ 3` returns error: "Not enough operands for operator `+`" |
| P0-08 | Return a descriptive error for division by zero | `5 0 /` returns error: "Division by zero is undefined" |
| P0-09 | Return a descriptive error for factorial of a negative number | `-3 !` returns error: "Factorial is undefined for negative numbers" |
| P0-10 | Return a descriptive error for factorial of a non-integer | `3.5 !` returns error: "Factorial requires a non-negative integer" |
| P0-11 | Return a descriptive error for unknown tokens | `3 4 $` returns error: "Unknown token `$`" |

#### History persistence

| # | Requirement | Acceptance criteria |
|---|---|---|
| P0-12 | Store every successful calculation (expression, result, timestamp) in MySQL | Row is present in `calculations` table immediately after a successful evaluation |
| P0-13 | Store every failed calculation (expression, error message, timestamp) in MySQL | Error entries are distinguishable from successes in the DB (e.g., `status` column) |
| P0-14 | Display calculation history on the UI (most recent first) | On page load, all persisted entries render; newest entry appears at the top |
| P0-15 | Allow the user to clear all history | Clicking "Clear history" removes all rows from the table and clears the UI list |

#### API design

| # | Requirement | Acceptance criteria |
|---|---|---|
| P0-16 | `POST /api/calculate` accepts `{ "expression": "3 4 +" }` and returns `{ "result": 7, "expression": "3 4 +", "id": <id> }` or an error payload | Response matches schema; HTTP 200 for success, HTTP 422 for evaluation errors |
| P0-17 | `GET /api/history` returns paginated list of all calculations, newest first | Response includes `data[]`, `total`, `per_page`, `current_page` |
| P0-18 | `DELETE /api/history` deletes all calculation records | Returns HTTP 204; subsequent `GET /api/history` returns empty `data[]` |

#### Frontend

| # | Requirement | Acceptance criteria |
|---|---|---|
| P0-19 | Single-page Angular app with an expression input field, a submit button, and a result display area | All three elements visible without scrolling on a 1280×720 viewport |
| P0-20 | Submit expression on Enter key press (in addition to button click) | Pressing Enter in the input field triggers evaluation |
| P0-21 | Display result or error message below the input field after evaluation | Result appears within 500ms of submit on a local network |
| P0-22 | Render the history list below the result display | History list updates immediately after each successful or failed evaluation |

---

### 5.2 Nice-to-Have — P1

- **Re-use from history**: Clicking a history entry populates the input field with that expression so the user can re-run or modify it.
- **Copy result to clipboard**: A one-click copy button next to the result.
- **Keyboard shortcut reference**: A collapsible "?" panel listing all supported operators with examples.
- **Input validation feedback**: Real-time indicator (e.g., token count vs. operator count) that hints at whether the expression is structurally sound before the user submits.
- **Individual history deletion**: Allow deleting a single history entry rather than only bulk-clear.
- **Responsive layout**: Functional and readable on viewports down to 375px (mobile).

---

### 5.3 Future Considerations — P2

- **User accounts and personal history**: Once auth is added, history becomes per-user rather than global.
- **Shareable expression links**: A URL such as `/calc?expr=3+4+%2B` that pre-populates the input, enabling sharing.
- **Extended operator set**: `log`, `ln`, `sqrt`, `sin`, `cos`, `tan` (scientific mode).
- **Arbitrary-precision mode**: Opt-in high-precision arithmetic for financial or cryptographic use cases.
- **Expression labelling**: Allow users to annotate history entries with a name/note.
- **API rate limiting & abuse protection**: Relevant once the app is public-facing at scale.

---

## 6. System Architecture Overview

```
┌──────────────────────────────┐        ┌─────────────────────────────┐
│  Angular SPA (frontend)      │  HTTP  │  Laravel API (backend)      │
│  - Expression input          │ ──────▶│  POST /api/calculate        │
│  - Result display            │        │  GET  /api/history          │
│  - History list              │ ◀──────│  DELETE /api/history        │
└──────────────────────────────┘  JSON  └────────────┬────────────────┘
                                                      │ Eloquent ORM
                                              ┌───────▼────────┐
                                              │  MySQL          │
                                              │  calculations   │
                                              └────────────────┘
```

### RPN evaluation — algorithm

The standard stack-based algorithm applies:

1. Tokenise the expression on whitespace.
2. For each token:
   - If it is a number (integer or float), push onto the stack.
   - If it is a binary operator (`+`, `-`, `*`, `/`, `^`), pop two operands (error if < 2 on stack), apply the operator, push the result.
   - If it is a unary operator (`%`, `!`), pop one operand (error if stack empty), apply the operator, push the result.
   - Otherwise, return an "unknown token" error.
3. After all tokens are processed, the stack must contain exactly one value — that is the result. If more than one value remains, the expression is malformed (error: "Too many operands — expression is incomplete").

This algorithm is entirely stateless per-request and belongs in a dedicated `RpnEvaluator` service class in Laravel, keeping the controller thin.

---

## 7. Data Model

### `calculations` table

| Column | Type | Notes |
|---|---|---|
| `id` | BIGINT UNSIGNED, PK | Auto-increment |
| `expression` | VARCHAR(1000) | Raw expression string as entered |
| `result` | DECIMAL(30, 10) NULL | NULL on error |
| `error_message` | VARCHAR(500) NULL | NULL on success |
| `status` | ENUM('success', 'error') | Derived from result/error |
| `evaluated_at` | TIMESTAMP | `DEFAULT CURRENT_TIMESTAMP` |

Index on `evaluated_at DESC` for history queries.

> **Domain note on floating-point storage:** `DECIMAL(30,10)` preserves up to 10 decimal places without the binary representation drift of `FLOAT`/`DOUBLE` columns — important when results are later displayed or compared. For most engineering use, this precision is sufficient; a `DOUBLE` column is acceptable if storage cost is a concern, with the caveat of potential display rounding artefacts.

---

## 8. Success Metrics

### Leading indicators (measure at launch + 1 week)

| Metric | Target |
|---|---|
| Calculation success rate | ≥ 95% of submitted expressions evaluate without a server 5xx error |
| Mean API response time (`POST /api/calculate`) | ≤ 200ms at p95 on staging |
| Error message clarity | 100% of validation errors return a human-readable `message` field (no raw stack traces) |

### Lagging indicators (measure at launch + 4 weeks)

| Metric | Target |
|---|---|
| History retrieval usage | ≥ 30% of sessions include at least one `GET /api/history` call |
| Repeat usage | ≥ 40% of unique sessions return within 7 days (proxy for utility) |
| Bug report rate | < 2 correctness bugs reported per 100 calculations (tracked via issue tracker) |

---

## 9. Open Questions

| # | Question | Owner | Blocking? |
|---|---|---|---|
| OQ-01 | What is the maximum integer value for `!` before overflow? Laravel/PHP can use `bcmath` for big integers — should we support `100 !`? | Engineering | No — default to max `20!` (fits in DECIMAL(30,10)) and document the limit |
| OQ-02 | Is history global (all users see all calculations) or session-scoped (anonymous session ID)? | Product | Yes — defines DB schema and API design |
| OQ-03 | Should the `DECIMAL(30,10)` column be widened or replaced with a string to preserve exact scientific-notation output (e.g., `1.5e-20`)? | Engineering | No — decide before DB migration is authored |
| OQ-04 | What is the expected concurrent user load? This affects whether a connection pool tuning or queue is needed for write-heavy history scenarios. | Engineering / Infra | No |
| OQ-05 | Do error entries count toward the history display, or only successful evaluations? | Product | No — UX preference |

---

## 10. Timeline Considerations

| Milestone | Date | Notes |
|---|---|---|
| DB schema + Laravel migrations done | 2026-05-10 | Unblocks backend API development |
| `RpnEvaluator` service + unit tests complete | 2026-05-14 | All operators, error cases, and edge inputs covered |
| REST API (`/calculate`, `/history`) complete | 2026-05-18 | Integration tests against MySQL |
| Angular SPA v1 (input + result + history) | 2026-05-24 | Wired to local Laravel API |
| End-to-end testing + bug fixes | 2026-05-28 | All P0 acceptance criteria pass |
| **Production deployment** | **2026-05-31** | Hard deadline |

> OQ-02 (history scoping) must be resolved before the DB migration milestone on 2026-05-10, as the schema differs depending on the answer.

---

## 11. Engineering Best Practices — Notes for the Team

- **Separation of concerns**: The RPN evaluation logic must live in a standalone service class (`App\Services\RpnEvaluator`) with no database or HTTP dependencies — this makes it trivially unit-testable in isolation.
- **Laravel validation**: Use Form Request classes to validate that `expression` is a non-empty string before it ever reaches the evaluator.
- **Angular reactive forms**: Use `ReactiveFormsModule` over template-driven forms for the expression input; it integrates more cleanly with RxJS for debouncing and submit handling.
- **API error contract**: All error responses should follow a consistent JSON shape `{ "message": "...", "code": "DIVISION_BY_ZERO" }` so the Angular service can handle them uniformly.
- **Database migrations**: Never modify existing migration files after they are committed. Add new migrations for schema changes.
- **Floating-point display**: Trim trailing zeros from results in the UI (e.g., show `6.28` not `6.2800000000`) using Angular's `DecimalPipe` with appropriate format strings.

---

*Document owner: Naveen — naveen@agilemania.com*  
*Last updated: 2026-05-03*

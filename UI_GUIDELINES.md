# RPN Calculator UI Guidelines

## Purpose

This document defines the visual and interaction rules for the RPN Calculator frontend so future work stays consistent with the production styling system introduced in the Angular app.

Use this document as the default reference for:

- new page and component styling
- layout decisions
- spacing and typography choices
- interaction and accessibility behavior
- extending the calculator UI with history, helper panels, and future tools

## Design Direction

The product should feel:

- precise
- trustworthy
- fast
- engineering-oriented
- modern without looking generic

The UI is intentionally not playful. It should feel like an instrument panel: sharp typography, clear hierarchy, strong contrast, and restrained motion.

## Core Visual Language

### Overall tone

- Use a dark interface as the default visual theme.
- Favor deep blue-slate surfaces over pure black.
- Use teal as the primary action color and sky blue as the accent.
- Use glass-like panels sparingly and only where they improve depth, not as decoration everywhere.

### Product personality

- Headings should feel confident and technical.
- Interactive areas should feel responsive and tactile.
- Data surfaces should feel stable and readable.
- Result states should feel prominent and rewarding.
- Error states should feel clear and corrective, not alarming or harsh.

## Design Tokens

These are the canonical design tokens currently implemented in [frontend/src/styles.scss](/Users/naveenkumarsingh/projects/learning/RPN_calc/frontend/src/styles.scss).

### Color tokens

- `--bg-main`: application background
- `--bg-panel`: primary translucent panel surface
- `--bg-panel-strong`: stronger solid panel surface
- `--bg-card`: secondary card surface
- `--bg-elevated`: input and trace background
- `--primary`: primary action color
- `--primary-soft`: focus, emphasis, and supportive highlight color
- `--accent`: informational accent color
- `--text-main`: default foreground text
- `--text-muted`: secondary body text
- `--text-faint`: placeholders and low-emphasis text
- `--border`: standard border color
- `--border-strong`: emphasized border for featured panels
- `--danger`: error color
- `--success`: success color
- `--warning`: caution color

### Radius tokens

- `--radius-sm`: small controls
- `--radius-md`: cards and emphasis surfaces
- `--radius-lg`: major panels

### Layout tokens

- `--content-width`: main content width cap

### Shadow tokens

- `--shadow-panel`: panel elevation shadow
- `--shadow-focus`: keyboard focus ring

## Typography

### Primary font

- Use `"IBM Plex Sans", "Segoe UI", sans-serif` for general UI copy.

### Monospace font

- Use `"IBM Plex Mono", Consolas, monospace` for:
  - expressions
  - operators
  - results
  - stack traces or evaluation steps
  - chip-like examples

### Typography rules

- Keep headings dense and slightly tight in letter spacing.
- Keep body text airy enough for scanability.
- Use uppercase micro-labels only for small context markers like section kickers or result labels.
- Do not overuse uppercase for main content.
- Avoid low-contrast small text.

## Layout Rules

### Page shell

- Constrain the main app width using `--content-width`.
- Maintain generous top padding so the UI feels intentional rather than cramped.
- Keep the hero compact and clear.

### Calculator layout

- Use a two-column layout on desktop:
  - main workspace on the left
  - support information or history on the right
- Collapse to one column below `860px`.
- Keep the calculation form above result and error feedback.
- Keep supporting context visually lighter than the primary calculation surface.

### Responsive behavior

- The calculator must remain functional and readable down to `375px`.
- Long result values must wrap safely.
- Primary actions must remain easy to tap.
- Avoid horizontal scrolling for any normal calculation flow.

## Component Guidelines

### Panels

- Use panel containers for major sections.
- Panels should have subtle borders, medium-large radius, and elevated shadow.
- Use backdrop blur only as an enhancement, not as the sole source of depth.
- Add slight top-edge highlight when useful for polish.

### Inputs

- Expression input should be visually prominent.
- Use monospace for expression entry.
- Input height should support quick scanning and touch usability.
- Placeholder text must stay legible but lower emphasis than entered text.
- Always provide visible `:focus-visible` treatment.

### Buttons

- Primary buttons use `--primary` background with dark text for contrast.
- Hover should slightly brighten the button and optionally lift it by `1px`.
- Disabled buttons must clearly communicate inactivity using reduced opacity and no hover lift.
- Never rely on color change alone for focus state; use the shared focus ring.

### Pills and chips

- Use chip styles for examples, operators, and low-priority metadata.
- Example chips may be informational or interactive, but the styling should reflect their role consistently.
- Avoid making decorative chips look clickable if they do nothing.

### Result surfaces

- Results should have stronger emphasis than standard panels.
- Use a gradient or elevated surface only where it reinforces importance.
- Result numbers should use monospace and support wrapping.
- Keep the result label small and clearly secondary.

### Error states

- Error surfaces should be distinct from result surfaces.
- Use warm red tones with readable text contrast.
- Error copy should be direct and instructional when possible.
- Avoid oversized alert patterns for simple validation feedback.

### Help and support sections

- Use support panels for examples, operator references, or evaluation walkthroughs.
- Support content should feel adjacent to the main task, not like a separate product area.
- Keep support copy concise and scan-friendly.

## Spacing Rules

- Prefer spacing rhythm in the `8px` family.
- Use tighter spacing inside chips and controls.
- Use more generous spacing between sections than between control-label pairs.
- Do not stack unrelated sections without visible breathing room.

Practical defaults:

- control gap: `0.5rem` to `0.75rem`
- section gap: `1rem` to `1.5rem`
- panel padding: `1.2rem` to `1.5rem`

## Motion Rules

- Motion should be subtle and purposeful.
- Prefer hover lift, shadow changes, and focus transitions over large transforms.
- Keep transitions around `0.2s`.
- Support `prefers-reduced-motion` globally.
- Do not animate layout in ways that delay the core calculator flow.

## Accessibility Rules

- Maintain strong contrast for all interactive and essential text.
- Every interactive control must have a visible `:focus-visible` state.
- Use semantic labels for inputs.
- Use `aria-live` for result and error messaging where appropriate.
- Do not use color as the only indicator of state.
- Keep tap targets comfortably sized for mobile use.

## Content Guidelines

### Labels and copy

- Prefer direct, concise, technical language.
- Avoid marketing-heavy language inside the workspace.
- Helper text should clarify expected format or constraints.
- Error text should explain what went wrong in plain language.

### Examples

- Example expressions should be short, representative, and valid.
- Prefer examples that map directly to supported operators.
- Avoid advanced examples unless the relevant features are already shipped.

## Implementation Rules

- Put global tokens and reset-like rules in [frontend/src/styles.scss](/Users/naveenkumarsingh/projects/learning/RPN_calc/frontend/src/styles.scss).
- Put app shell layout styles in [frontend/src/app/app.scss](/Users/naveenkumarsingh/projects/learning/RPN_calc/frontend/src/app/app.scss).
- Put feature-specific view styling in the feature component stylesheet, such as [frontend/src/app/features/calculator/pages/calculator-page.component.scss](/Users/naveenkumarsingh/projects/learning/RPN_calc/frontend/src/app/features/calculator/pages/calculator-page.component.scss).
- Reuse existing tokens before introducing new raw values.
- If a new raw value appears more than once, promote it to a token.

## Extension Rules For Future Features

### History UI

- History should appear as a stable data surface, not a decorative feed.
- Timestamp, expression, and result/error should have clear hierarchy.
- Reusable row patterns should preserve scanability at high entry counts.

### Operator help

- Operator reference should use chips, lists, or compact cards.
- Keep examples monospace.
- Do not bury core input behind large help content.

### Empty states

- Empty states should be calm and brief.
- Explain what the user can do next.
- Avoid illustration-heavy empty states for this product.

## Anti-Patterns

Avoid the following:

- bright purple accents
- flat white cards on dark pages
- overly rounded consumer-style controls
- generic dashboard chrome that competes with the calculator task
- weak contrast on helper text
- clickable-looking elements with no action
- animations that make calculation feel slower
- mixing too many accent colors in one view

## Working Agreement Going Forward

When adding or revising UI in this project:

- start from these guidelines first
- preserve the dark engineering-focused visual direction
- keep accessibility and keyboard use as first-class concerns
- prefer small system extensions over one-off styling
- update this document if the design system changes materially

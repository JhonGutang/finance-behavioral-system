# Spendlytic: Finance Behavioral System

> **Current Phase:** MVP - Refinement  
> **Status:** Behavioral Features, CSV Support, & Dynamic Dashboard Implemented

---

## Project Overview

An engineering-focused finance tracker that explores how **deterministic feedback** can help users improve financial habits over time. Instead of just tracking transactions, the system **detects spending patterns** and **generates adaptive feedback** to guide behavior.

> [!IMPORTANT]
> This is an **engineering exploration**, not a production finance app. Focus is on system design, rule-based logic, and adaptive behavior.

> [!CAUTION]
> **STRICT UI STANDARD:** All visual modifications MUST strictly adhere to the **Disciplined UI** rulings (Button height `h-11`, `rounded-full`, etc.). Failure to follow the layout hierarchy in [`docs/design-system.md`](./docs/design-system.md) is considered a breaking change.

### Current Scope (MVP)

**Building:** Finance tracker with adaptive feedback engine including:

- Transaction tracking (CSV upload or manual entry)
- Pattern detection using 3 deterministic rules
- Template-based feedback generation
- Adaptive feedback that evolves with user progress
- Dashboard/CLI output for viewing results

**Reference:** See [`docs/low-concept.md`](file:///c:/Users/jhonb/Documents/Websites/spendlytic/docs/low-concept.md) for MVP overview and [`docs/finance-tracking.md`](file:///c:/Users/jhonb/Documents/Websites/spendlytic/docs/finance-tracking.md) for detailed system design.

### Future Roadmap

Planned features (**NOT** for current implementation):

- Additional pattern detection rules
- Advanced feedback templates
- Simulation and replay features
- Multi-user support
- UI dashboard with charts
- Bank integrations
- Budgeting Tools
- Theming (Light/Dark Mode)

> [!IMPORTANT]
> Focus on MVP scope only. Do NOT implement future features unless explicitly requested.

---

## Development Standards

### Code Quality

**Type Safety:**

- All frontend code MUST be strictly typed (TypeScript)
- No `any` types unless absolutely necessary and documented

**Code Clarity:**

- Descriptive, self-explanatory names
- Avoid complex conditionals in a single statement. Break them down into intermediate variables.
- Comments only for complex logic
- Proper formatting, spacing, indentation

**Import Style (TypeScript):**

- Avoid inline type imports (e.g., `Promise<import('../types').PaginatedResponse<Transaction>>`)
- ALWAYS use top-level `import type` statements for external types

**Import Style (PHP):**

- Avoid inline namespace usage (e.g., `$history = \App\Models\FeedbackHistory::where(...)`).
- ALWAYS import classes at the top level using `use` statements.

**Method Signatures (PHP):**

- When a method requires more than 2 parameters, create a Data Transfer Object (DTO) to encapsulate them.
- This ensures a cleaner and more readable structure.

**Constants & Config:**

- Magic numbers → Named constants (UPPER_CASE)
- Configuration in `config/` folder
- Sensitive data in environment variables only

**Abstraction:**

- Appropriate levels of abstraction
- Single responsibility per function
- Extract complex logic into well-named helpers

### UI & Visual Excellence

**Disciplined UI Standard:**

- **Buttons:** MUST be `h-11`, `rounded-full`, `uppercase font-bold`, and `tracking-widest`.
- **Contrast:** NEVER use low-opacity text for critical info. Use high-contrast, solid colors.
- **Typography:** Titles in `font-serif`, data/body in `font-inter`.
- **Tone:** Use the "Financial Garden" botanical terminology (Seeds, Harvest, Wisdom).
- **Reference:** Always follow [`docs/design-system.md`](./docs/design-system.md).

### Workflow

1. **Before coding:** Read `@AGENTS.MD` and `@docs`
2. **During:** Follow architecture patterns below
3. **After:** Write tests → Run tests → Update docs
4. **Always:** Document mistakes and learnings

### Testing

- **Backend:** Pest (write tests AFTER implementation)
- **Frontend:** Jest (write tests AFTER implementation)
- **Focus:** Validate desired behavior, not just coverage

> [!CAUTION]
> **Test Case Preservation:** Once test cases are created and verified to be working correctly with the expected behavior, they MUST NOT be modified in future implementations. New features should add NEW test cases, not alter existing ones. Modifying working tests breaks the regression safety net and is strictly forbidden unless fixing an actual bug in the test itself.

### When to Ask

Ask for confirmation if:

- Requirements are ambiguous
- Multiple viable approaches exist
- Significant architectural decisions needed

---

## Architecture

### Monorepo Structure

```
/
├── app/
│   ├── backend/     # Laravel API
│   └── web/         # Vue frontend
└── docs/            # System documentation
```

### Backend (Laravel)

**Pattern:** Clean Architecture

```
Controller → Service → Repository → Model
```

**Directories:** `libs/`, `utils/`, `config/`

### Frontend (Vue + TypeScript)

**Pattern:** Component-based Architecture

```
View → Component → Composable → Store → Service → Model
```

**Directories:** `libs/`, `utils/`, `config/`

> [!IMPORTANT]
> **Type Checking:** Before running ANY command (build, dev, test, etc.), ALWAYS run `npm run type-check` first to ensure there are no TypeScript errors. Never proceed with commands if type errors exist.

> [!NOTE]
> See [`docs/finance-tracking.md`](file:///c:/Users/jhonb/Documents/Websites/spendlytic/docs/finance-tracking.md) for detailed architecture decisions and layer responsibilities.

---

## Tech Stack

### Frontend

- Vue 3 + TypeScript
- shadcn-vue (UI)
- Tailwind CSS
- Pinia (State)
- Jest (Testing)

### Communication

- Axios

### Backend

- Laravel (latest stable)
- MySQL
- Pest (Testing)

> [!NOTE]
> Always reference official documentation for current best practices.

---

## Quick Reference

### Do's ✓

- Read AGENTS.MD and docs before starting
- Use TypeScript strict mode
- Follow defined architecture patterns
- Write tests after implementation
- Update documentation after changes
- Use environment variables for secrets
- Ask for clarification when uncertain

### Don'ts ✗

- Implement future features prematurely
- Use `any` type without justification
- Hardcode sensitive information
- Add unnecessary comments
- Skip testing
- Deviate from architecture without discussion

---

## Documentation

**MVP Documentation:**

- **[system-overview.md](./docs/system-overview.md)** - Logic flow and architecture overview
- **[low-concept.md](./docs/low-concept.md)** - MVP overview and project scope
- **[finance-tracking.md](./docs/finance-tracking.md)** - Detailed system design, data models, architecture
- **[features.md](./docs/features.md)** - Current implemented features lookup
- **[design-system.md](./docs/design-system.md)** - UI/UX standards and design philosophy
- **[rule-engine.md](./docs/rule-engine.md)** - Pattern detection rules and evaluation logic
- **[finance-habit-tracker.md](./docs/finance-habit-tracker.md)** - Adaptive feedback system architecture
- **[date-formatter.md](./docs/date-formatter.md)** - Date formatting utility documentation

**Future Reference (NOT for MVP):**

- **[mid-concept.md](file:///c:/Users/jhonb/Documents/Websites/spendlytic/docs/mid-concept.md)** - Extended concept and design philosophy (advanced features)

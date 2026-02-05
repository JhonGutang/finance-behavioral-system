# Spendlytic: Finance Behavioral System

> **Current Phase:** MVP - Adaptive Feedback Engine  
> **Status:** Initial Development

---

## Project Overview

An engineering-focused finance tracker that explores how **deterministic feedback** can help users improve financial habits over time. Instead of just tracking transactions, the system **detects spending patterns** and **generates adaptive feedback** to guide behavior.

> [!IMPORTANT]
> This is an **engineering exploration**, not a production finance app. Focus is on system design, rule-based logic, and adaptive behavior.

### Current Scope (MVP)

**Building:** Finance tracker with adaptive feedback engine including:
- Transaction tracking (CSV upload or manual entry)
- Pattern detection using 3 deterministic rules
- Template-based feedback generation
- Adaptive feedback that evolves with user progress
- Dashboard/CLI output for viewing results

**Reference:** See [`docs/low-concept.md`](file:///c:/Users/jhonb/Documents/Websites/finance-behavioral-system/docs/low-concept.md) for MVP overview and [`docs/finance-tracking.md`](file:///c:/Users/jhonb/Documents/Websites/finance-behavioral-system/docs/finance-tracking.md) for detailed system design.

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
- Comments only for complex logic
- Proper formatting, spacing, indentation

**Import Style (PHP):**
- Avoid inline namespace imports (e.g., `private \App\Services\Service $service`)
- ALWAYS use `use` statements at the top of the file for all classes and types

**Constants & Config:**
- Magic numbers → Named constants (UPPER_CASE)
- Configuration in `config/` folder
- Sensitive data in environment variables only

**Abstraction:**
- Appropriate levels of abstraction
- Single responsibility per function
- Extract complex logic into well-named helpers

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
> See [`docs/finance-tracking.md`](file:///c:/Users/jhonb/Documents/Websites/finance-behavioral-system/docs/finance-tracking.md) for detailed architecture decisions and layer responsibilities.

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
- **[system-overview.md](file:///c:/Users/jhonb/Documents/Websites/spendlytic/docs/system-overview.md)** - Logic flow and architecture overview
- **[low-concept.md](file:///c:/Users/jhonb/Documents/Websites/spendlytic/docs/low-concept.md)** - MVP overview and project scope
- **[finance-tracking.md](file:///c:/Users/jhonb/Documents/Websites/spendlytic/docs/finance-tracking.md)** - Detailed system design, data models, architecture
- **[features.md](file:///c:/Users/jhonb/Documents/Websites/spendlytic/docs/features.md)** - Current implemented features lookup
- **[rule-engine.md](file:///c:/Users/jhonb/Documents/Websites/spendlytic/docs/rule-engine.md)** - Pattern detection rules and evaluation logic
- **[finance-habit-tracker.md](file:///c:/Users/jhonb/Documents/Websites/spendlytic/docs/finance-habit-tracker.md)** - Adaptive feedback system architecture
- **[date-formatter.md](file:///c:/Users/jhonb/Documents/Websites/spendlytic/docs/date-formatter.md)** - Date formatting utility documentation

**Future Reference (NOT for MVP):**
- **[mid-concept.md](file:///c:/Users/jhonb/Documents/Websites/finance-behavioral-system/docs/mid-concept.md)** - Extended concept and design philosophy (advanced features)
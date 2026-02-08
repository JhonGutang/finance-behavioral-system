---
description: Run the local CI/CD pipeline checks (types, lint, build, tests)
---

# System Check Workflow

This workflow mirrors the CI/CD pipeline locally to ensure code quality and stability before committing.

## Backend Checks

// turbo
1. Run Backend Linting (Code Style)
```bash
cd app/backend && ./vendor/bin/pint --test
```

// turbo
2. Run Backend Tests (Pest)
```bash
cd app/backend && php artisan test
```

## Frontend Checks

// turbo
3. Run Frontend Type Checking
```bash
cd app/web && npx vue-tsc --noEmit
```

// turbo
4. Run Frontend Tests (Vitest)
```bash
cd app/web && npm run test
```

// turbo
5. Run Frontend Production Build
```bash
cd app/web && npm run build
```

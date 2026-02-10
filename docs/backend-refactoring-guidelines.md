# Backend Refactoring Guidelines

> A prioritized guide to violations found against our [AGENTS.MD](../AGENTS.MD) coding standards.
> **Investigation Date:** February 11, 2026

---

## Priority Levels

| Priority | Description | Impact |
|----------|-------------|--------|
| 🔴 **URGENT** | Architecture violations — breaks Clean Architecture pattern | High structural debt |
| 🟡 **HIGH** | Import/namespace violations — directly violates AGENTS.MD rules | Non-compliant code |
| 🟠 **MEDIUM** | Method signature violations — readability & maintainability | Growing complexity |
| 🔵 **LOW** | Style inconsistencies — cosmetic but worth standardizing | Minor tech debt |

---

## 🔴 URGENT — Architecture Violations

These violations break the `Controller → Service → Repository → Model` pattern defined in AGENTS.MD.

### 1. `AnalyticsService.php` — Direct Model Queries (Bypasses Repository)

**All 3 methods** (`getDailyFlow`, `getMonthlyFlow`, `getYearlyFlow`) query `Transaction::where(...)` directly instead of going through `TransactionRepository`.

```php
// ❌ Current — Direct Eloquent queries in Service layer
$transactions = Transaction::where('user_id', $userId)
    ->whereDate('date', '>=', $startDate->format('Y-m-d'))
    ->whereDate('date', '<=', $endDate->format('Y-m-d'))
    ->get();
```

```php
// ✅ Expected — Delegate to Repository
$transactions = $this->transactionRepository->getByDateRange($startDate, $endDate, $userId);
```

**Files affected:** [AnalyticsService.php](../app/backend/app/Services/AnalyticsService.php)
**Lines:** 21-24, 73-76, 125

**Action:**
- [ ] Create new repository methods for daily/monthly/yearly queries (or reuse `getByDateRange`)
- [ ] Remove direct `Transaction::where()` calls from `AnalyticsService`

---

### 2. `FeedbackEngineService.php` — Direct Model Query (Bypasses Repository)

The `getFeedbackHistory()` method at line 240 queries `FeedbackHistory::where(...)` directly instead of using `FeedbackHistoryRepository`.

```php
// ❌ Current — Direct query in Service layer
private function getFeedbackHistory(int $userId, string $ruleId): Collection
{
    return FeedbackHistory::where('user_id', $userId)
        ->where('rule_id', $ruleId)
        ->orderBy('week_start', 'desc')
        ->limit(4)
        ->get();
}
```

```php
// ✅ Expected — Use existing repository
private function getFeedbackHistory(int $userId, string $ruleId): Collection
{
    return $this->feedbackHistoryRepository->getByUserAndRule($userId, $ruleId);
}
```

**Files affected:** [FeedbackEngineService.php](../app/backend/app/Services/FeedbackEngineService.php)
**Lines:** 240-247

**Action:**
- [ ] Add `getByUserAndRule(int $userId, string $ruleId, int $limit = 4)` method to `FeedbackHistoryRepository`
- [ ] Replace direct query in `FeedbackEngineService`
- [ ] Remove the `use App\Models\FeedbackHistory` import (no longer needed)

---

### 3. `CsvImportService.php` — Direct Model Queries (Bypasses Repository)

Two methods make direct Eloquent calls:

**a) `findOrCreateCategory()` (line 131)** — queries `Category::where(...)` directly instead of using `CategoryRepository`.

```php
// ❌ Current
$category = Category::where('name', $name)
    ->where('type', $type)
    ->where(function ($query) use ($userId) { ... })
    ->first();
```

**b) `checkIsDuplicate()` (line 157)** — queries `Transaction::where(...)` directly instead of using `TransactionRepository`.

```php
// ❌ Current
return Transaction::where('user_id', $userId)
    ->where('date', $data['date'])
    ->where('amount', $data['amount'])
    ->where('type', $data['type'])
    ->where('description', $data['description'])
    ->exists();
```

**Files affected:** [CsvImportService.php](../app/backend/app/Services/CsvImportService.php)
**Lines:** 131-152, 157-163

**Action:**
- [ ] Add `findByNameTypeAndUser(string $name, string $type, int $userId)` to `CategoryRepository`
- [ ] Add `isDuplicate(array $data, int $userId)` to `TransactionRepository`
- [ ] Replace direct queries in `CsvImportService`
- [ ] Remove unused model imports (`use App\Models\Category`, `use App\Models\Transaction`)

---

## 🟡 HIGH — Import/Namespace Violations

These directly violate the AGENTS.MD rule: *"Avoid inline namespace usage. ALWAYS import classes at the top level using `use` statements."*

### 4. `TransactionService.php` — Inline `\App\Models\Category`

```php
// ❌ Line 129 — Inline namespace
$category = \App\Models\Category::find($value);
```

```php
// ✅ Fix — Already imported but not used here; replace inline reference
use App\Models\Category; // Already exists at line 5
// ...
$category = Category::find($value);
```

> [!NOTE]
> `App\Models\Transaction` is already properly imported at line 5. However, the `Category` model is **not** imported — it's only used inline at line 129.

**Files affected:** [TransactionService.php](../app/backend/app/Services/TransactionService.php)
**Line:** 129

**Action:**
- [ ] Add `use App\Models\Category;` to imports
- [ ] Replace `\App\Models\Category::find()` with `Category::find()`

---

### 5. `CategoryService.php` — Inline `\Illuminate\Validation\Rule` and `\Exception`

**a) Inline `\Illuminate\Validation\Rule`** (line 98):
```php
// ❌ Inline namespace usage
\Illuminate\Validation\Rule::unique('categories', 'name')
```

**b) Inline `\Exception`** (line 76):
```php
// ❌ Inline exception
throw new \Exception('Cannot delete category with existing transactions');
```

**Files affected:** [CategoryService.php](../app/backend/app/Services/CategoryService.php)
**Lines:** 76, 98

**Action:**
- [ ] Add `use Illuminate\Validation\Rule;` to imports
- [ ] Add `use Exception;` to imports (or consider a custom exception)
- [ ] Replace inline usages with imported class names

---

### 6. `CsvImportService.php` — Inline `\Exception` (2 occurrences)

```php
// ❌ Line 51 — Inline exception in catch block
} catch (\Exception $e) {

// ❌ Line 123 — Inline exception in throw
throw new \Exception(implode(', ', $validator->errors()->all()));
```

**Files affected:** [CsvImportService.php](../app/backend/app/Services/CsvImportService.php)
**Lines:** 51, 123

**Action:**
- [ ] Add `use Exception;` to imports
- [ ] Replace both `\Exception` occurrences with `Exception`

---

### 7. `CategoryController.php` — Inline `\Exception`

```php
// ❌ Line 120 — Inline exception
} catch (\Exception $e) {
```

**Files affected:** [CategoryController.php](../app/backend/app/Http/Controllers/Api/CategoryController.php)
**Line:** 120

**Action:**
- [ ] Add `use Exception;` to imports
- [ ] Replace `\Exception` with `Exception`

---

## 🟠 MEDIUM — Method Signature Violations (DTO Needed)

These violate the AGENTS.MD rule: *"When a method requires more than 2 parameters, create a DTO to encapsulate them."*

### 8. `TransactionRepository.php` — `getFilteredPaginated()` (3 params)

```php
// ❌ Current — 3 parameters
public function getFilteredPaginated(int $userId, array $filters, int $perPage = 10)
```

**Files affected:** [TransactionRepository.php](../app/backend/app/Repositories/TransactionRepository.php)
**Line:** 30

**Action:**
- [ ] Create `TransactionFilterDTO` with properties: `userId`, `filters`, `perPage`
- [ ] Refactor `getFilteredPaginated()` to accept the DTO
- [ ] Update `TransactionService.getFilteredPaginated()` call site

---

### 9. `TransactionRepository.php` — `getWeeklySummary()` (3 params)

```php
// ❌ Current — 3 parameters
public function getWeeklySummary(int $userId, string $startDate, string $endDate): array
```

**Files affected:** [TransactionRepository.php](../app/backend/app/Repositories/TransactionRepository.php)
**Line:** 222

**Action:**
- [ ] Create `DateRangeQueryDTO` with properties: `userId`, `startDate`, `endDate`
- [ ] Refactor `getWeeklySummary()` to accept the DTO
- [ ] Update `RuleEngineService.evaluateRules()` call sites

---

### 10. `TransactionRepository.php` — `getLastUpdateTimestamp()` (3 params)

```php
// ❌ Current — 3 parameters
public function getLastUpdateTimestamp(int $userId, string $startDate, string $endDate): ?string
```

**Files affected:** [TransactionRepository.php](../app/backend/app/Repositories/TransactionRepository.php)
**Line:** 257

**Action:**
- [ ] Reuse the `DateRangeQueryDTO` (same shape as `getWeeklySummary`)
- [ ] Refactor `getLastUpdateTimestamp()` to accept the DTO

---

### 11. `TransactionService.php` — `updateTransaction()` (3 params)

```php
// ❌ Current — 3 parameters
public function updateTransaction(int $id, int $userId, array $data): bool
```

**Files affected:** [TransactionService.php](../app/backend/app/Services/TransactionService.php)
**Line:** 80

**Action:**
- [ ] Create `TransactionUpdateDTO` with properties: `id`, `userId`, `data`
- [ ] Refactor `updateTransaction()` and corresponding controller call site

---

### 12. `TransactionService.php` — `getFilteredPaginated()` (3 params)

```php
// ❌ Current — 3 parameters
public function getFilteredPaginated(int $userId, array $filters, int $perPage = 10)
```

**Files affected:** [TransactionService.php](../app/backend/app/Services/TransactionService.php)
**Line:** 25

**Action:**
- [ ] Reuse `TransactionFilterDTO` from item #8

---

### 13. `TransactionService.php` — `getTransactionsByDateRange()` (3 params)

```php
// ❌ Current — 3 parameters
public function getTransactionsByDateRange(string $startDate, string $endDate, int $userId): Collection
```

**Files affected:** [TransactionService.php](../app/backend/app/Services/TransactionService.php)
**Line:** 41

**Action:**
- [ ] Reuse `DateRangeQueryDTO` from item #9

---

### 14. `CsvImportService.php` — `findOrCreateCategory()` (3 params)

```php
// ❌ Current — 3 parameters
private function findOrCreateCategory(string $name, string $type, int $userId): Category
```

**Files affected:** [CsvImportService.php](../app/backend/app/Services/CsvImportService.php)
**Line:** 129

**Action:**
- [ ] Create `CategoryLookupDTO` with properties: `name`, `type`, `userId`
- [ ] Refactor `findOrCreateCategory()` to accept the DTO

---

### 14b. `FeedbackEngineService.php` — `enrichAdvancedData()` (3 params)

```php
// ❌ Current — 3 parameters
private function enrichAdvancedData(array $data, string $ruleId, int $userId): array
```

**Files affected:** [FeedbackEngineService.php](../app/backend/app/Services/FeedbackEngineService.php)
**Line:** 228

**Action:**
- [ ] Consider creating an `AdvancedDataEnrichmentDTO` or reuse context from the existing `FeedbackTemplateInputDTO`

---

### 14c. `TransactionRepository.php` — `getByDateRange()` (3 params)

```php
// ❌ Current — 3 parameters
public function getByDateRange(string $startDate, string $endDate, int $userId): Collection
```

**Files affected:** [TransactionRepository.php](../app/backend/app/Repositories/TransactionRepository.php)
**Line:** 78

**Action:**
- [ ] Reuse `DateRangeQueryDTO` from item #9

---

### 15. `CategoryService.php` — `updateCategory()` (3 params)

```php
// ❌ Current — 3 parameters
public function updateCategory(int $id, int $userId, array $data): bool
```

**Files affected:** [CategoryService.php](../app/backend/app/Services/CategoryService.php)
**Line:** 59

**Action:**
- [ ] Create `CategoryUpdateDTO` with properties: `id`, `userId`, `data`
- [ ] Refactor `updateCategory()` and the corresponding controller call site

---

### 16. `CategoryRepository.php` — `update()` and `delete()` (3 params)

```php
// ❌ Current — 3 parameters
public function update(int $id, int $userId, array $data): bool
public function delete(int $id, int $userId): bool  // Only 2, but pairs with update
```

`update()` has 3 params. `delete()` only has 2 so it's fine.

**Files affected:** [CategoryRepository.php](../app/backend/app/Repositories/CategoryRepository.php)
**Line:** 60

**Action:**
- [ ] Reuse `CategoryUpdateDTO` from item #15

---

### 17. `TransactionRepository.php` — `update()` and `delete()` (3 params for update)

```php
// ❌ Current — 3 parameters
public function update(int $id, int $userId, array $data): bool
```

**Files affected:** [TransactionRepository.php](../app/backend/app/Repositories/TransactionRepository.php)
**Line:** 123

**Action:**
- [ ] Reuse `TransactionUpdateDTO` from item #11

---

## 🔵 LOW — Style Inconsistencies

### 18. `AuthService.php` & `AuthController.php` — Inconsistent Constructor Style

These two files use the **traditional** constructor style while the rest of the codebase uses **PHP 8 promoted properties**.

```php
// ❌ Current — Old-style constructor (AuthService.php)
protected UserRepository $userRepository;

public function __construct(UserRepository $userRepository)
{
    $this->userRepository = $userRepository;
}
```

```php
// ✅ Expected — PHP 8 promoted properties (consistent with rest of codebase)
public function __construct(
    private UserRepository $userRepository
) {}
```

**Files affected:**
- [AuthService.php](../app/backend/app/Services/AuthService.php) (lines 11-16)
- [AuthController.php](../app/backend/app/Http/Controllers/Api/AuthController.php) (lines 14-19)

**Action:**
- [ ] Refactor to promoted properties in `AuthService`
- [ ] Refactor to promoted properties in `AuthController`

---

### 19. `FeedbackEngineService.php` — Magic Numbers

Several magic numbers without named constants:

| Line | Value | Context |
|------|-------|---------|
| 138 | `2` | Threshold for consecutive violations → advanced feedback |
| 140 | `2` | Threshold for consecutive improvements → advanced feedback |
| 156 | `50` | Default/base improvement score |
| 159 | `50` | Base score for calculation |
| 163 | `3` | Total rule count (used in score calculation) |
| 163 | `10` | Score delta per rule not triggered |
| 170 | `15` | Bonus for improvement |
| 172 | `10` | Penalty for regression |
| 204 | `0.9` | Target amount reduction factor (10%) |
| 245 | `4` | History lookup limit |

**Files affected:** [FeedbackEngineService.php](../app/backend/app/Services/FeedbackEngineService.php)

**Action:**
- [ ] Extract into named `private const` values (e.g., `CONSECUTIVE_THRESHOLD = 2`, `BASE_IMPROVEMENT_SCORE = 50`, `TOTAL_RULE_COUNT = 3`, `SCORE_PER_RULE = 10`, `IMPROVEMENT_BONUS = 15`, `REGRESSION_PENALTY = 10`, `TARGET_REDUCTION_FACTOR = 0.9`, `HISTORY_LOOKUP_LIMIT = 4`)

---

### 20. `TransactionRepository.php` — Magic Number in `getWeeklySummary()`

```php
// ❌ Line 231 — Magic number 10 (small transaction threshold)
->where('transactions.amount', '<', 10)
```

This value **should** come from `RuleEngineService::SMALL_PURCHASE_AMOUNT_LIMIT` or a shared constant, not be hardcoded.

**Files affected:** [TransactionRepository.php](../app/backend/app/Repositories/TransactionRepository.php)
**Lines:** 231, 243

**Action:**
- [ ] Accept the small purchase limit as a parameter or reference a shared constant
- [ ] Ensure consistency with `RuleEngineService::SMALL_PURCHASE_AMOUNT_LIMIT` (currently `10.00`)

---

### 21. `TransactionRepository.php` — `getSummary()` Method Complexity

The `getSummary()` method (lines 151-203) performs **both** basic summary calculation **and** month-over-month trend analysis in a single 50+ line method. This violates the **single responsibility** and **abstraction** guidelines.

**Files affected:** [TransactionRepository.php](../app/backend/app/Repositories/TransactionRepository.php)
**Lines:** 151-203

**Action:**
- [ ] Extract trend calculation into a separate private method (e.g., `calculateTrends()`)
- [ ] Consider whether trend logic belongs in the Service layer instead of the Repository

---

## Summary — Proposed DTOs

| DTO Name | Properties | Used By |
|----------|-----------|---------|
| `TransactionFilterDTO` | `userId`, `filters`, `perPage` | `TransactionRepository`, `TransactionService` |
| `DateRangeQueryDTO` | `userId`, `startDate`, `endDate` | `TransactionRepository`, `TransactionService`, `RuleEngineService` |
| `TransactionUpdateDTO` | `id`, `userId`, `data` | `TransactionRepository`, `TransactionService` |
| `CategoryUpdateDTO` | `id`, `userId`, `data` | `CategoryRepository`, `CategoryService` |
| `CategoryLookupDTO` | `name`, `type`, `userId` | `CsvImportService` |

> [!NOTE]
> **Constructor injection** with >2 dependencies (e.g., `TransactionController`, `RuleEngineController`) is **not** a DTO violation. The AGENTS.MD rule about DTOs applies specifically to **method parameters**, not dependency injection constructors. DI constructors are managed by Laravel's service container and are expected to have multiple dependencies.

---

## Clean Files (No Violations Found) ✅

These files comply with all current AGENTS.MD guidelines:

- `RuleEngineService.php` — Clean architecture, proper constants, proper imports
- `FeedbackTemplateLibrary.php` — Clean static utility
- `FeedbackController.php` — Clean controller
- `HealthController.php` — Clean controller
- `TransactionController.php` — Clean controller (constructor DI is not a DTO violation)
- `RuleEngineController.php` — Clean controller (constructor DI is not a DTO violation)
- `FeedbackHistoryRepository.php` — Clean repository
- `UserProgressRepository.php` — Clean repository
- `UserRepository.php` — Clean repository
- `FeedbackTemplateInputDTO.php` — Good DTO pattern (reference for new DTOs)
- All Models (`User.php`, `Transaction.php`, `Category.php`, `FeedbackHistory.php`, `UserProgress.php`)
- `LoginFormRequest.php`, `RegisterFormRequest.php`
- `AppServiceProvider.php`

---

## Recommended Refactoring Order

1. **🔴 URGENT items first** (1-3) — Fix architecture violations before they multiply
2. **🟡 HIGH items** (4-7) — Quick wins, simple import fixes
3. **🟠 MEDIUM items** (8-17) — DTO creation, batch related DTOs together
4. **🔵 LOW items** (18-21) — Clean up during natural development flow

> [!TIP]
> Run `php artisan test` after each refactoring to ensure no regressions. Each item is scoped small enough to be a single commit.

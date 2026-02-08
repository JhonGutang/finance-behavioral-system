# Rule Engine - Pattern Detection System

> **Document Type:** Technical Specification  
> **Phase:** MVP - Adaptive Feedback Engine  
> **Status:** Implemented  
> **Last Updated:** 2026-02-07

---

## Table of Contents

1. [Overview](#overview)
2. [Pattern Detection Rules](#pattern-detection-rules)
3. [Rule Evaluation Logic](#rule-evaluation-logic)
4. [Implementation Details](#implementation-details)
5. [Testing Approach](#testing-approach)
6. [Future Extensions](#future-extensions)

---

## Overview

The Rule Engine is the core pattern detection component of the Finance Behavioral System. It analyzes transaction history using **deterministic, heuristic-based rules** to identify spending patterns that trigger feedback generation.

### Design Principles

- **Deterministic:** Same input always produces same output
- **Explainable:** Every rule trigger can be traced step-by-step
- **Testable:** Rules can be validated with sample data
- **Extensible:** New rules can be added without changing core logic

### Rule Philosophy

Rules are **signals, not conclusions**. They detect patterns in transaction data and trigger feedback generation, but they don't make assumptions about user psychology or intent.

---

## Pattern Detection Rules

The MVP implements **3 core rules** that detect common spending patterns:

### Rule 1: Category Overspend

**Description:** Detects when spending in a specific category exceeds the previous week's spending by a significant margin.

**Trigger Condition:**
```
Current Week Category Spending > Previous Week Category Spending × 1.25
```

**Parameters:**
- **Threshold:** 25% increase
- **Time Window:** 7 days (1 week)
- **Comparison:** Week-over-week
- **Scope:** Per category

**Example:**
```
Previous Week: Food & Dining = ₱200
Current Week: Food & Dining = ₱260
Increase: 30% (triggers rule because 30% > 25%)
```

**Data Required:**
- Transaction history for current week
- Transaction history for previous week
- Category grouping

**Output:**
```json
{
  "rule_id": "category_overspend",
  "triggered": true,
  "category": "Food & Dining",
  "current_week_amount": 260.00,
  "previous_week_amount": 200.00,
  "increase_percentage": 30.0,
  "threshold": 25.0
}
```

---

### Rule 2: Weekly Spending Spike

**Description:** Detects when total weekly spending (across all categories) is significantly higher than the previous week.

**Trigger Condition:**
```
Current Week Total Spending > Previous Week Total Spending × 1.20
```

**Parameters:**
- **Threshold:** 20% increase
- **Time Window:** 7 days (1 week)
- **Comparison:** Week-over-week
- **Scope:** All expense transactions

**Example:**
```
Previous Week Total: ₱500
Current Week Total: ₱650
Increase: 30% (triggers rule because 30% > 20%)
```

**Data Required:**
- Sum of all expense transactions for current week
- Sum of all expense transactions for previous week

**Output:**
```json
{
  "rule_id": "weekly_spending_spike",
  "triggered": true,
  "current_week_total": 650.00,
  "previous_week_total": 500.00,
  "increase_percentage": 30.0,
  "threshold": 20.0
}
```

---

### Rule 3: Frequent Small Purchases

**Description:** Detects when a user makes many small transactions in a short period, which often indicates impulse spending or lack of awareness.

**Trigger Condition:**
```
Count of transactions with amount < ₱10 in current week >= 10
```

**Parameters:**
- **Amount Threshold:** ₱10.00
- **Count Threshold:** 10 transactions
- **Time Window:** 7 days (1 week)
- **Scope:** All expense transactions

**Example:**
```
Current Week Small Transactions:
- Coffee: ₱4.50
- Snack: ₱3.00
- Coffee: ₱4.50
- ... (7 more transactions under ₱10)
Total Count: 10+ (triggers rule)
```

**Data Required:**
- All expense transactions for current week
- Filter by amount < ₱10.00
- Count of matching transactions

**Output:**
```json
{
  "rule_id": "frequent_small_purchases",
  "triggered": true,
  "transaction_count": 12,
  "amount_threshold": 10.00,
  "count_threshold": 10,
  "total_amount": 78.50,
  "average_amount": 6.54
}
```

---

## Rule Evaluation Logic

### Evaluation Flow

```
Transaction Data
    ↓
Weekly Aggregation
    ↓
Rule Evaluation (all 3 rules in parallel)
    ↓
Triggered Rules Collection
    ↓
Feedback Generation (see finance-habit-tracker.md)
```

### Evaluation Timing

In the current implementation, rules are evaluated:
1. **On-demand (API-driven):** Triggered by the frontend via `GET /api/rule-engine/evaluate`.
2. **Dashboard Initialization:** When a user visits the Insights or Dashboard view.

> [!NOTE]
> **Real-time Observers**: While the system is designed to support Laravel Model Observers (triggering on every transaction save), this is currently **not active** in the MVP to minimize feedback fatigue.

### Week Definition

- **Week Start:** Monday 00:00:00
- **Week End:** Sunday 23:59:59
- **Timezone:** User's local timezone

### Edge Cases

#### No Previous Week Data
- **Behavior:** Rule does not trigger
- **Rationale:** Cannot compare without baseline
- **User Feedback:** "Not enough data for comparison"

#### First Week of Usage
- **Behavior:** Rules 1 and 2 do not trigger
- **Rationale:** No previous week to compare
- **User Feedback:** "Building your baseline..."

#### Zero Spending in Category
- **Previous Week:** ₱0
- **Current Week:** Any amount > ₱0
- **Behavior:** Rule does not trigger
- **Rationale:** Avoid false positives for new spending categories

#### Partial Week Data
- **Behavior:** Evaluate based on available data
- **Note:** May produce less accurate results
- **Future Enhancement:** Require minimum data threshold

---

## 🔄 Adaptive Feedback Logic (MVP Refinement)

The system now implements two levels of feedback that adapt based on user behavior over time.

### Feedback Levels

1.  **Basic Level**: Targeted at users who are struggling with a specific rule (triggered 2+ weeks in a row) or new users. Focuses on simple, direct actions.
2.  **Advanced Level**: Targeted at users who have shown improvement (rule NOT triggered for 2+ weeks in a row). Focuses on historical context and optimization.

### Advanced Metric Enrichment

To support advanced feedback, the `FeedbackEngineService` now calculates additional metrics on-the-fly:

| Metric | Rule | Calculation |
| :--- | :--- | :--- |
| **4-Week Average** | Category Overspend | Average spending in that category over the last 4 successful/failed weeks. |
| **Baseline 4-Week average** | Weekly Spike | Average total weekly spending over the last month. |
| **Average Small Purchase** | Frequent Small Purchases | Sum of small purchases / count of small purchases. |

---

## Testing Approach

### Unit Tests

Each rule should have comprehensive unit tests covering:

1. **Happy Path:** Rule triggers correctly when conditions are met
2. **No Trigger:** Rule does not trigger when conditions are not met
3. **Edge Cases:** Boundary conditions, zero values, missing data
4. **Data Validation:** Invalid inputs are handled gracefully

#### Example Test Cases

**Category Overspend:**
```typescript
describe('Category Overspend Rule', () => {
  test('triggers when spending increases by 25%+', () => {
    const previous = createWeeklySummary({ 'Food': 200 });
    const current = createWeeklySummary({ 'Food': 260 });
    const result = checkCategoryOverspend(current, previous);
    expect(result.triggered).toBe(true);
  });
  
  test('does not trigger when spending increases by less than 25%', () => {
    const previous = createWeeklySummary({ 'Food': 200 });
    const current = createWeeklySummary({ 'Food': 240 });
    const result = checkCategoryOverspend(current, previous);
    expect(result.triggered).toBe(false);
  });
  
  test('does not trigger when previous week has zero spending', () => {
    const previous = createWeeklySummary({ 'Food': 0 });
    const current = createWeeklySummary({ 'Food': 100 });
    const result = checkCategoryOverspend(current, previous);
    expect(result.triggered).toBe(false);
  });
});
```

### Integration Tests

Test the complete rule evaluation flow:
- Load transaction data
- Generate weekly summaries
- Evaluate all rules
- Verify correct rules trigger
- Verify feedback generation (see finance-habit-tracker.md)

### Test Data

Create realistic test datasets:
- **Baseline Week:** Normal spending patterns
- **Spike Week:** Significant increases
- **Small Purchases Week:** Many small transactions
- **Mixed Week:** Multiple rules trigger

---

## Implementation Details

### Data Structures

#### Transaction Event
```typescript
interface Transaction {
  id: string;
  type: 'income' | 'expense';
  amount: number;
  date: Date;
  category_name: string;
  user_id: string;
  description?: string;
}
```

#### Weekly Summary
```typescript
interface WeeklySummary {
  user_id: string;
  week_start: Date;
  week_end: Date;
  total_income: number;
  total_expenses: number;
  category_totals: Map<string, number>;
  transaction_count: number;
  small_transaction_count: number;
}
```

#### Rule Result
```typescript
interface RuleResult {
  rule_id: string;
  triggered: boolean;
  user_id: string;
  timestamp: Date;
  data: Record<string, any>;
  displayed: boolean;
}
```

---

## Future Extensions

### Additional Rules (Phase 2+)

1. **Income-Spending Correlation**
   - Detect when spending increases proportionally with income increases
   - Helps identify lifestyle inflation

2. **Time-of-Day Patterns**
   - Detect late-night or early-morning spending patterns
   - May indicate emotional spending

3. **Day-of-Week Patterns**
   - Detect weekend vs weekday spending differences
   - Helps identify social spending patterns

4. **Merchant Frequency**
   - Detect repeated purchases from same merchant
   - May indicate subscription or habit spending

5. **Category Neglect**
   - Detect when essential categories have zero spending
   - May indicate budgeting issues

### Rule Configuration

Future enhancement: Allow users to customize thresholds
```typescript
interface RuleConfig {
  rule_id: string;
  enabled: boolean;
  parameters: {
    threshold?: number;
    time_window?: number;
    // ... other configurable parameters
  };
}
```

### Rule Priority

Future enhancement: Assign priority levels to rules
- **High Priority:** Rules that indicate significant issues
- **Medium Priority:** Rules that suggest improvements
- **Low Priority:** Rules that provide insights

### Composite Rules

Future enhancement: Rules that combine multiple conditions
```typescript
// Example: Category overspend AND frequent small purchases
if (categoryOverspend.triggered && frequentSmallPurchases.triggered) {
  // Generate more specific feedback
}
```

---

## Appendix

### Glossary

- **Rule:** A deterministic condition that detects a spending pattern
- **Trigger:** When a rule's conditions are met
- **Threshold:** The numeric boundary that determines if a rule triggers
- **Time Window:** The period over which data is analyzed
- **Weekly Summary:** Aggregated transaction data for a 7-day period

### References

- See [`low-concept.md`](file:///c:/Users/jhonb/Documents/Websites/spendlytic/docs/low-concept.md) for MVP overview
- See [`finance-habit-tracker.md`](file:///c:/Users/jhonb/Documents/Websites/spendlytic/docs/finance-habit-tracker.md) for feedback generation
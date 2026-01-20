# Finance Habit Tracker - Adaptive Feedback System

> **Document Type:** Technical Specification  
> **Phase:** MVP - Adaptive Feedback Engine  
> **Last Updated:** 2026-01-20

---

## Table of Contents

1. [Overview](#overview)
2. [Feedback System Architecture](#feedback-system-architecture)
3. [Feedback Templates](#feedback-templates)
4. [Adaptive Feedback Levels](#adaptive-feedback-levels)
5. [Progress Tracking](#progress-tracking)
6. [Implementation Details](#implementation-details)
7. [Testing Approach](#testing-approach)

---

## Overview

The Finance Habit Tracker is the **adaptive feedback component** of the Finance Behavioral System. It generates personalized, actionable feedback based on detected spending patterns and adapts over time based on user progress.

### Core Concept

Instead of just showing what happened, the system:
1. **Detects patterns** using the Rule Engine (see [`rule-engine.md`](file:///c:/Users/jhonb/Documents/Websites/finance-behavioral-system/docs/rule-engine.md))
2. **Generates feedback** using structured templates
3. **Adapts suggestions** based on user progress over time
4. **Tracks improvement** to determine feedback level

### Design Goals

- **Deterministic:** Feedback generation is predictable and explainable
- **Adaptive:** Feedback evolves based on measurable progress
- **Actionable:** Every feedback includes specific suggested actions
- **Non-judgmental:** Focus on patterns and data, not psychology

---

## Feedback System Architecture

### Feedback Generation Pipeline

```
Rule Triggers (from Rule Engine)
    ↓
Match Feedback Templates
    ↓
Select Appropriate Feedback Level
    ↓
Fill Template Placeholders
    ↓
Generate Feedback Message
    ↓
Store Feedback History
    ↓
Display to User
```

### Components

1. **Template Library:** Collection of feedback templates for each rule
2. **Progress Tracker:** Monitors user improvement over time
3. **Level Selector:** Determines appropriate feedback complexity
4. **Placeholder Engine:** Fills templates with computed values
5. **Feedback Store:** Persists feedback history for replay

---

## Feedback Templates

### Template Structure

Each feedback template contains:

```typescript
interface FeedbackTemplate {
  template_id: string;
  rule_id: string;
  level: 'basic' | 'advanced';
  explanation: string;           // Why this feedback is shown
  suggestion: string;            // What the user should do
  placeholders: string[];        // Variables to fill (e.g., ${amount})
  priority: number;              // Display priority (1-10)
}
```

### Template Examples

#### Rule 1: Category Overspend

**Basic Level Template:**
```typescript
{
  template_id: 'category_overspend_basic',
  rule_id: 'category_overspend',
  level: 'basic',
  explanation: 'You spent ${current_amount} in ${category} this week, which is ${increase_percentage}% higher than last week (${previous_amount}).',
  suggestion: 'Try limiting ${category} spending to ${target_amount} next week.',
  placeholders: ['current_amount', 'category', 'increase_percentage', 'previous_amount', 'target_amount'],
  priority: 8
}
```

**Advanced Level Template:**
```typescript
{
  template_id: 'category_overspend_advanced',
  rule_id: 'category_overspend',
  level: 'advanced',
  explanation: 'Your ${category} spending increased by ${increase_percentage}% (${current_amount} vs ${previous_amount}). Over the past 4 weeks, your average is ${four_week_average}.',
  suggestion: 'Consider setting a weekly ${category} budget of ${recommended_budget} based on your 4-week trend. Track daily to stay aware.',
  placeholders: ['category', 'increase_percentage', 'current_amount', 'previous_amount', 'four_week_average', 'recommended_budget'],
  priority: 8
}
```

---

#### Rule 2: Weekly Spending Spike

**Basic Level Template:**
```typescript
{
  template_id: 'weekly_spike_basic',
  rule_id: 'weekly_spending_spike',
  level: 'basic',
  explanation: 'Your total spending this week (${current_total}) is ${increase_percentage}% higher than last week (${previous_total}).',
  suggestion: 'Review your transactions to identify unexpected expenses. Try to reduce discretionary spending next week.',
  placeholders: ['current_total', 'increase_percentage', 'previous_total'],
  priority: 9
}
```

**Advanced Level Template:**
```typescript
{
  template_id: 'weekly_spike_advanced',
  rule_id: 'weekly_spending_spike',
  level: 'advanced',
  explanation: 'Weekly spending increased ${increase_percentage}% to ${current_total}. Main contributors: ${top_categories}. Your 4-week average is ${four_week_average}.',
  suggestion: 'Focus on reducing ${highest_category} by ${reduction_target}. Set daily spending limit of ${daily_limit} to stay on track.',
  placeholders: ['increase_percentage', 'current_total', 'top_categories', 'four_week_average', 'highest_category', 'reduction_target', 'daily_limit'],
  priority: 9
}
```

---

#### Rule 3: Frequent Small Purchases

**Basic Level Template:**
```typescript
{
  template_id: 'small_purchases_basic',
  rule_id: 'frequent_small_purchases',
  level: 'basic',
  explanation: 'You made ${transaction_count} small purchases (under ${amount_threshold}) this week, totaling ${total_amount}.',
  suggestion: 'Small purchases add up quickly. Try consolidating purchases or setting a daily spending limit.',
  placeholders: ['transaction_count', 'amount_threshold', 'total_amount'],
  priority: 6
}
```

**Advanced Level Template:**
```typescript
{
  template_id: 'small_purchases_advanced',
  rule_id: 'frequent_small_purchases',
  level: 'advanced',
  explanation: '${transaction_count} small purchases totaling ${total_amount} (avg: ${average_amount}). Common times: ${peak_times}. Common merchants: ${top_merchants}.',
  suggestion: 'Implement a "wait 24 hours" rule for purchases under ${amount_threshold}. Prepare alternatives (e.g., bring coffee from home) to reduce impulse spending.',
  placeholders: ['transaction_count', 'total_amount', 'average_amount', 'peak_times', 'top_merchants', 'amount_threshold'],
  priority: 6
}
```

---

## Adaptive Feedback Levels

### Two-Level System

The MVP implements **2 feedback levels** that adapt based on user progress:

1. **Basic Level:** Simpler explanations, straightforward suggestions
2. **Advanced Level:** Detailed analysis, sophisticated strategies

### Level Selection Logic

```typescript
function selectFeedbackLevel(userProgress: UserProgress): FeedbackLevel {
  const recentWeeks = userProgress.last_4_weeks;
  
  // Count consecutive weeks of rule violations
  const consecutiveViolations = countConsecutiveViolations(recentWeeks);
  
  // Count consecutive weeks of improvement
  const consecutiveImprovements = countConsecutiveImprovements(recentWeeks);
  
  if (consecutiveViolations >= 2) {
    // User is struggling → simpler advice
    return 'basic';
  }
  
  if (consecutiveImprovements >= 2) {
    // User is improving → more advanced advice
    return 'advanced';
  }
  
  // Default to basic for new users or mixed progress
  return 'basic';
}
```

### Progress States

#### Struggling State
- **Condition:** Same rule triggered 2+ weeks in a row
- **Feedback Level:** Basic
- **Approach:** Simpler, more actionable suggestions
- **Example:** "Try limiting Food spending to $200 next week"

#### Improving State
- **Condition:** Rule did NOT trigger for 2+ weeks in a row (after previously triggering)
- **Feedback Level:** Advanced
- **Approach:** More detailed analysis, optimization strategies
- **Example:** "Your 4-week average is $180. Consider setting a $170 budget to continue improving"

#### Stable State
- **Condition:** Mixed results, no clear pattern
- **Feedback Level:** Basic
- **Approach:** Maintain awareness, reinforce positive behavior
- **Example:** "Keep tracking your spending to maintain awareness"

---

## Progress Tracking

### Data Model

#### User Progress Record
```typescript
interface UserProgress {
  user_id: string;
  week_start: Date;
  week_end: Date;
  rules_triggered: string[];           // IDs of rules that triggered
  rules_not_triggered: string[];       // IDs of rules that did not trigger
  feedback_generated: FeedbackMessage[];
  improvement_score: number;           // 0-100 scale
}
```

#### Feedback Message
```typescript
interface FeedbackMessage {
  feedback_id: string;
  timestamp: Date;
  rule_id: string;
  template_id: string;
  level: 'basic' | 'advanced';
  explanation: string;                 // Filled template
  suggestion: string;                  // Filled template
  data: Record<string, any>;          // Raw data used
  displayed: boolean;
  user_acknowledged: boolean;
}
```

### Progress Metrics

#### Improvement Score Calculation
```typescript
function calculateImprovementScore(history: UserProgress[]): number {
  if (history.length < 2) return 50; // Neutral for new users
  
  const recent = history.slice(-4); // Last 4 weeks
  let score = 50;
  
  // Positive factors
  for (const week of recent) {
    // Fewer rules triggered = better
    score += (3 - week.rules_triggered.length) * 5;
    
    // Consistent improvement = better
    if (week.rules_triggered.length < previous.rules_triggered.length) {
      score += 10;
    }
  }
  
  // Normalize to 0-100
  return Math.max(0, Math.min(100, score));
}
```

#### Consecutive Violation Tracking
```typescript
function countConsecutiveViolations(history: UserProgress[], ruleId: string): number {
  let count = 0;
  
  // Start from most recent week and count backwards
  for (let i = history.length - 1; i >= 0; i--) {
    if (history[i].rules_triggered.includes(ruleId)) {
      count++;
    } else {
      break; // Stop at first week without violation
    }
  }
  
  return count;
}
```

---

## Implementation Details

### Placeholder Filling

```typescript
function fillTemplate(
  template: FeedbackTemplate,
  data: Record<string, any>
): FeedbackMessage {
  let explanation = template.explanation;
  let suggestion = template.suggestion;
  
  // Replace all placeholders with actual values
  for (const placeholder of template.placeholders) {
    const value = data[placeholder];
    
    // Format value based on type
    const formattedValue = formatValue(value, placeholder);
    
    // Replace ${placeholder} with formatted value
    explanation = explanation.replace(`\${${placeholder}}`, formattedValue);
    suggestion = suggestion.replace(`\${${placeholder}}`, formattedValue);
  }
  
  return {
    feedback_id: generateId(),
    timestamp: new Date(),
    rule_id: template.rule_id,
    template_id: template.template_id,
    level: template.level,
    explanation,
    suggestion,
    data,
    displayed: false,
    user_acknowledged: false
  };
}

function formatValue(value: any, placeholder: string): string {
  // Currency formatting
  if (placeholder.includes('amount') || placeholder.includes('total') || placeholder.includes('budget')) {
    return `$${value.toFixed(2)}`;
  }
  
  // Percentage formatting
  if (placeholder.includes('percentage')) {
    return `${value.toFixed(1)}%`;
  }
  
  // Default: convert to string
  return String(value);
}
```

### Feedback Storage

#### Database Schema

**`feedback_history` Table:**
```sql
CREATE TABLE feedback_history (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  week_start DATE NOT NULL,
  week_end DATE NOT NULL,
  rule_id VARCHAR(50) NOT NULL,
  template_id VARCHAR(50) NOT NULL,
  level ENUM('basic', 'advanced') NOT NULL,
  explanation TEXT NOT NULL,
  suggestion TEXT NOT NULL,
  data JSON NOT NULL,
  displayed BOOLEAN DEFAULT FALSE,
  user_acknowledged BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_week (user_id, week_start),
  INDEX idx_rule (rule_id)
);
```

**`user_progress` Table:**
```sql
CREATE TABLE user_progress (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  week_start DATE NOT NULL,
  week_end DATE NOT NULL,
  rules_triggered JSON NOT NULL,
  rules_not_triggered JSON NOT NULL,
  improvement_score INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_week (user_id, week_start)
);
```

---

## Testing Approach

### Unit Tests

#### Template Filling
```typescript
describe('Template Filling', () => {
  test('fills all placeholders correctly', () => {
    const template = {
      explanation: 'You spent ${amount} in ${category}',
      placeholders: ['amount', 'category']
    };
    
    const data = { amount: 250.50, category: 'Food' };
    const result = fillTemplate(template, data);
    
    expect(result.explanation).toBe('You spent $250.50 in Food');
  });
});
```

#### Level Selection
```typescript
describe('Feedback Level Selection', () => {
  test('selects basic level for struggling users', () => {
    const progress = createProgressHistory([
      { rules_triggered: ['category_overspend'] },
      { rules_triggered: ['category_overspend'] }
    ]);
    
    const level = selectFeedbackLevel(progress);
    expect(level).toBe('basic');
  });
  
  test('selects advanced level for improving users', () => {
    const progress = createProgressHistory([
      { rules_triggered: [] },
      { rules_triggered: [] }
    ]);
    
    const level = selectFeedbackLevel(progress);
    expect(level).toBe('advanced');
  });
});
```

### Integration Tests

Test complete feedback generation flow:
1. Trigger rule with sample data
2. Verify correct template selected
3. Verify placeholders filled correctly
4. Verify feedback stored in database
5. Verify progress tracking updated

### Simulation Tests

Create multi-week scenarios to test adaptive behavior:
- **Scenario 1:** User consistently violates rules → feedback stays basic
- **Scenario 2:** User improves over time → feedback becomes advanced
- **Scenario 3:** User has mixed results → feedback adapts appropriately

---

## Future Enhancements

### Multi-Level Feedback (Phase 2+)

Expand from 2 levels to 3+ levels:
- **Beginner:** Very simple, one action at a time
- **Intermediate:** Multiple suggestions, prioritized
- **Advanced:** Detailed analysis, optimization strategies
- **Expert:** Proactive insights, trend predictions

### Personalized Templates

Allow customization based on user preferences:
- Tone (encouraging vs. direct)
- Detail level (concise vs. comprehensive)
- Focus (saving vs. awareness)

### Feedback Effectiveness Tracking

Measure whether feedback leads to behavior change:
```typescript
interface FeedbackEffectiveness {
  feedback_id: string;
  rule_triggered_before: boolean;
  rule_triggered_after: boolean;
  improvement_observed: boolean;
  weeks_to_improvement: number;
}
```

### Positive Reinforcement

Add feedback for positive behaviors:
- Spending below baseline
- Consistent improvement
- Milestone achievements

---

## Appendix

### Glossary

- **Feedback Template:** Structured message with placeholders
- **Feedback Level:** Complexity of feedback (basic vs. advanced)
- **Progress State:** User's current improvement status
- **Improvement Score:** Numeric measure of progress (0-100)
- **Consecutive Violations:** Number of weeks in a row a rule triggered

### References

- See [`rule-engine.md`](file:///c:/Users/jhonb/Documents/Websites/finance-behavioral-system/docs/rule-engine.md) for pattern detection rules
- See [`low-concept.md`](file:///c:/Users/jhonb/Documents/Websites/finance-behavioral-system/docs/low-concept.md) for MVP overview
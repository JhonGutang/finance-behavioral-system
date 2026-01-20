# Finance Tracking System - MVP Documentation

> **Document Type:** Detailed System Concepts & Design  
> **Phase:** MVP - Adaptive Feedback Engine  
> **Last Updated:** 2026-01-20

---

## Table of Contents

1. [System Overview](#system-overview)
2. [Feature Requirements](#feature-requirements)
3. [Data Model](#data-model)
4. [System Architecture](#system-architecture)
5. [UI/UX Concepts](#uiux-concepts)
6. [Technical Considerations](#technical-considerations)
7. [Future Enhancements](#future-enhancements)

---

## System Overview

### Purpose

The Finance Tracking System is an **engineering-focused finance tracker with an adaptive feedback engine**. Instead of just tracking transactions, the system detects spending patterns using deterministic rules and generates adaptive feedback to guide financial behavior over time.

> [!IMPORTANT]
> This is an **engineering exploration**, not a production finance app. Focus is on system design, rule-based logic, and adaptive behavior.

### Goals

- **Pattern Detection:** Identify spending patterns using deterministic rules
- **Adaptive Feedback:** Generate personalized suggestions that evolve with user progress
- **Behavioral Guidance:** Help users improve financial habits through structured feedback
- **Explainability:** Every feedback decision is traceable and testable

### User Personas

**Primary User:** Individual seeking to improve financial habits through feedback
- Needs to track transactions (manual or CSV upload)
- Wants to understand spending patterns
- Desires actionable feedback to improve behavior
- Appreciates progress tracking over time

### System Boundaries

**In Scope (MVP):**
- Transaction tracking (CSV upload or manual entry)
- Pattern detection using 3 deterministic rules
- Template-based feedback generation
- Adaptive feedback (2 levels: basic and advanced)
- Progress tracking over time
- Dashboard/CLI output for viewing results

**Out of Scope (MVP):**
- Machine learning or predictive modeling
- Emotional or psychological inference
- Real-time enforcement or spending blocks
- Bank integrations
- Multi-user support
- Multi-currency support

---

## Feature Requirements

### FR-001: Manual Transaction Input

**Description:** Users can manually create transaction records for both income and expenses.

**User Story:**
> As a user, I want to manually add transactions so that I can track my income and expenses.

**Acceptance Criteria:**
- ✓ User can create a new transaction
- ✓ User can specify transaction type (income or expense)
- ✓ User can enter transaction amount (positive decimal number)
- ✓ User can select transaction date
- ✓ User can select or create a category
- ✓ User can optionally add a description/note
- ✓ Transaction is saved to database
- ✓ User receives confirmation of successful save

**Validation Rules:**
- Amount must be greater than 0
- Date cannot be in the future
- Category must be selected
- Type (income/expense) must be specified

---

### FR-002: Transaction Categorization

**Description:** Transactions are organized into categories for better tracking and analysis.

**User Story:**
> As a user, I want to categorize my transactions so that I can understand where my money is going.

**Acceptance Criteria:**
- ✓ System provides default categories
- ✓ User can select from existing categories
- ✓ User can create custom categories
- ✓ Categories have names and optional colors/icons
- ✓ Each transaction is associated with exactly one category

**Default Categories:**

**Income:**
- Salary
- Freelance
- Investment
- Gift
- Other Income

**Expense:**
- Food & Dining
- Transportation
- Shopping
- Bills & Utilities
- Entertainment
- Healthcare
- Education
- Other Expense

---

### FR-003: Summary Dashboard

**Description:** Users see a summary of their financial status including totals and net balance.

**User Story:**
> As a user, I want to see a summary of my finances so that I can quickly understand my financial status.

**Acceptance Criteria:**
- ✓ Display total income (sum of all income transactions)
- ✓ Display total expenses (sum of all expense transactions)
- ✓ Display net balance (income - expenses)
- ✓ Summary updates in real-time when transactions are added/edited/deleted
- ✓ Summary can be filtered by date range (optional for MVP)

**Calculations:**
```
Total Income = SUM(transactions WHERE type = 'income')
Total Expenses = SUM(transactions WHERE type = 'expense')
Net Balance = Total Income - Total Expenses
```

---

### FR-004: Data Visualization

**Description:** Simple charts provide visual representation of transaction data.

**User Story:**
> As a user, I want to see charts of my transactions so that I can visualize my spending patterns.

**Acceptance Criteria:**
- ✓ Display chart showing income vs expenses
- ✓ Display chart showing expense breakdown by category
- ✓ Charts update when transactions change
- ✓ Charts are clear and easy to understand

**Chart Types:**
1. **Income vs Expense Bar Chart** - Compare total income and expenses
2. **Expense Pie Chart** - Show expense distribution by category

---

### FR-005: Transaction List

**Description:** Users can view a list of all their transactions.

**User Story:**
> As a user, I want to see a list of my transactions so that I can review my financial history.

**Acceptance Criteria:**
- ✓ Display all transactions in a list/table
- ✓ Show date, amount, category, type, and description
- ✓ Sort by date (newest first by default)
- ✓ User can edit existing transactions
- ✓ User can delete transactions
- ✓ List updates when transactions are added/edited/deleted

---

## Data Model

### Database Schema

#### `categories` Table

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| `name` | VARCHAR(100) | NOT NULL, UNIQUE | Category name |
| `type` | ENUM('income', 'expense') | NOT NULL | Category type |
| `color` | VARCHAR(7) | NULLABLE | Hex color code (e.g., #FF5733) |
| `icon` | VARCHAR(50) | NULLABLE | Icon identifier |
| `is_default` | BOOLEAN | DEFAULT FALSE | System-provided category |
| `created_at` | TIMESTAMP | NOT NULL | Record creation time |
| `updated_at` | TIMESTAMP | NOT NULL | Record update time |

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `type`

**Sample Data:**
```sql
INSERT INTO categories (name, type, is_default) VALUES
('Salary', 'income', true),
('Food & Dining', 'expense', true),
('Transportation', 'expense', true);
```

---

#### `transactions` Table

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| `type` | ENUM('income', 'expense') | NOT NULL | Transaction type |
| `amount` | DECIMAL(10, 2) | NOT NULL, CHECK (amount > 0) | Transaction amount |
| `date` | DATE | NOT NULL | Transaction date |
| `category_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY | Reference to categories |
| `description` | TEXT | NULLABLE | Optional notes |
| `created_at` | TIMESTAMP | NOT NULL | Record creation time |
| `updated_at` | TIMESTAMP | NOT NULL | Record update time |

**Indexes:**
- PRIMARY KEY on `id`
- FOREIGN KEY on `category_id` REFERENCES `categories(id)` ON DELETE RESTRICT
- INDEX on `date`
- INDEX on `type`
- INDEX on `category_id`

**Sample Data:**
```sql
INSERT INTO transactions (type, amount, date, category_id, description) VALUES
('income', 5000.00, '2026-01-15', 1, 'Monthly salary'),
('expense', 150.50, '2026-01-16', 2, 'Grocery shopping'),
('expense', 45.00, '2026-01-17', 3, 'Gas');
```

---

### Entity Relationships

```
categories (1) ──────< (many) transactions
```

**Relationship Rules:**
- One category can have many transactions
- Each transaction belongs to exactly one category
- Categories cannot be deleted if they have associated transactions (ON DELETE RESTRICT)

---

## System Architecture

### Architecture Decisions

#### Backend: Clean Architecture

**Rationale:** Separation of concerns makes the codebase maintainable and testable.

**Layers:**

1. **Controller Layer**
   - Handles HTTP requests/responses
   - Validates input data
   - Returns JSON responses
   - Example: `TransactionController`, `CategoryController`

2. **Service Layer**
   - Contains business logic
   - Orchestrates operations
   - Handles calculations (totals, balances)
   - Example: `TransactionService`, `CategoryService`, `SummaryService`

3. **Repository Layer**
   - Abstracts database operations
   - Provides clean data access interface
   - Example: `TransactionRepository`, `CategoryRepository`

4. **Model Layer**
   - Eloquent models representing database entities
   - Defines relationships and attributes
   - Example: `Transaction`, `Category`

**Example Flow:**
```
User Request
    ↓
TransactionController::store()
    ↓
TransactionService::createTransaction()
    ↓
TransactionRepository::create()
    ↓
Transaction Model
    ↓
Database
```

---

#### Frontend: Component-Based Architecture

**Rationale:** Reusable components with clear data flow improve maintainability and developer experience.

**Layers:**

1. **View Layer**
   - Page-level components
   - Route definitions
   - Example: `DashboardView`, `TransactionsView`

2. **Component Layer**
   - Reusable UI components
   - Presentational logic
   - Example: `TransactionForm`, `SummaryCard`, `ExpenseChart`

3. **Composable Layer**
   - Shared reactive logic (Vue Composition API)
   - Reusable state and methods
   - Example: `useTransactions`, `useSummary`

4. **Store Layer**
   - Global state management (Pinia)
   - Centralized data
   - Example: `transactionStore`, `categoryStore`

5. **Service Layer**
   - API communication
   - Data transformation
   - Example: `transactionService`, `categoryService`

6. **Model Layer**
   - TypeScript interfaces and types
   - Data structure definitions
   - Example: `Transaction`, `Category`, `Summary`

**Example Flow:**
```
User Interaction
    ↓
TransactionForm Component
    ↓
transactionStore (Pinia)
    ↓
transactionService
    ↓
API Call (Axios)
    ↓
Backend
```

---

### API Endpoints

#### Transactions

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/transactions` | List all transactions |
| POST | `/api/transactions` | Create new transaction |
| GET | `/api/transactions/{id}` | Get single transaction |
| PUT | `/api/transactions/{id}` | Update transaction |
| DELETE | `/api/transactions/{id}` | Delete transaction |
| GET | `/api/transactions/summary` | Get summary (totals, balance) |

#### Categories

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/categories` | List all categories |
| POST | `/api/categories` | Create new category |
| GET | `/api/categories/{id}` | Get single category |
| PUT | `/api/categories/{id}` | Update category |
| DELETE | `/api/categories/{id}` | Delete category (if no transactions) |

---

## UI/UX Concepts

### Screen Layouts

#### 1. Dashboard (Main Screen)

**Purpose:** Primary view showing financial overview

**Components:**
- Summary cards (Total Income, Total Expenses, Net Balance)
- Income vs Expense bar chart
- Expense breakdown pie chart
- Quick add transaction button

**Layout:**
```
┌─────────────────────────────────────────┐
│  Dashboard                              │
├─────────────────────────────────────────┤
│  ┌──────┐  ┌──────┐  ┌──────┐          │
│  │Income│  │Expense│ │Balance│          │
│  │$5000 │  │$2500 │ │$2500  │          │
│  └──────┘  └──────┘  └──────┘          │
│                                         │
│  ┌─────────────┐  ┌─────────────┐      │
│  │ Income vs   │  │  Expense    │      │
│  │  Expense    │  │  Breakdown  │      │
│  │  Bar Chart  │  │  Pie Chart  │      │
│  └─────────────┘  └─────────────┘      │
│                                         │
│  [+ Add Transaction]                    │
└─────────────────────────────────────────┘
```

---

#### 2. Transactions List

**Purpose:** View and manage all transactions

**Components:**
- Transaction table/list
- Filter/sort controls
- Add/Edit/Delete actions

**Layout:**
```
┌─────────────────────────────────────────┐
│  Transactions          [+ Add]          │
├─────────────────────────────────────────┤
│  Date       │ Category │ Amount │ Type  │
│  2026-01-17 │ Food     │ $150   │ Exp   │
│  2026-01-15 │ Salary   │ $5000  │ Inc   │
│  ...                                    │
└─────────────────────────────────────────┘
```

---

#### 3. Transaction Form (Modal/Drawer)

**Purpose:** Add or edit transaction

**Fields:**
- Type (Income/Expense) - Radio buttons or toggle
- Amount - Number input
- Date - Date picker
- Category - Dropdown/Select
- Description - Text area (optional)

**Actions:**
- Save
- Cancel

---

### User Workflows

#### Workflow 1: Add New Transaction

1. User clicks "Add Transaction" button
2. Transaction form modal opens
3. User selects type (Income/Expense)
4. User enters amount
5. User selects date (defaults to today)
6. User selects category from dropdown
7. User optionally adds description
8. User clicks "Save"
9. System validates input
10. System saves transaction to database
11. System updates summary and charts
12. System shows success message
13. Modal closes

#### Workflow 2: View Financial Summary

1. User navigates to Dashboard
2. System calculates totals from database
3. System displays summary cards
4. System renders charts
5. User views financial overview

#### Workflow 3: Edit Transaction

1. User clicks "Edit" on a transaction
2. Transaction form opens with pre-filled data
3. User modifies fields
4. User clicks "Save"
5. System validates input
6. System updates transaction in database
7. System updates summary and charts
8. System shows success message

---

### Design Principles

- **Minimalism:** Clean, uncluttered interface
- **Clarity:** Clear labels and visual hierarchy
- **Feedback:** Immediate visual feedback for actions
- **Consistency:** Consistent patterns across the application
- **Accessibility:** Proper contrast, keyboard navigation, ARIA labels

---

## Technical Considerations

### Validation Rules

#### Transaction Validation

```typescript
interface TransactionValidation {
  type: 'income' | 'expense';           // Required, must be one of two values
  amount: number;                        // Required, must be > 0, max 2 decimals
  date: Date;                           // Required, cannot be future date
  category_id: number;                  // Required, must exist in categories
  description?: string;                 // Optional, max 1000 characters
}
```

**Backend Validation (Laravel):**
```php
$rules = [
    'type' => 'required|in:income,expense',
    'amount' => 'required|numeric|min:0.01|max:9999999.99',
    'date' => 'required|date|before_or_equal:today',
    'category_id' => 'required|exists:categories,id',
    'description' => 'nullable|string|max:1000',
];
```

**Frontend Validation (Vue):**
- Real-time validation on input
- Display error messages below fields
- Disable submit button until valid

---

#### Category Validation

```typescript
interface CategoryValidation {
  name: string;                         // Required, unique, max 100 chars
  type: 'income' | 'expense';           // Required
  color?: string;                       // Optional, hex format
  icon?: string;                        // Optional
}
```

---

### Business Logic

#### Summary Calculations

```typescript
interface Summary {
  totalIncome: number;
  totalExpenses: number;
  netBalance: number;
  transactionCount: number;
}

function calculateSummary(transactions: Transaction[]): Summary {
  const income = transactions
    .filter(t => t.type === 'income')
    .reduce((sum, t) => sum + t.amount, 0);
  
  const expenses = transactions
    .filter(t => t.type === 'expense')
    .reduce((sum, t) => sum + t.amount, 0);
  
  return {
    totalIncome: income,
    totalExpenses: expenses,
    netBalance: income - expenses,
    transactionCount: transactions.length
  };
}
```

---

### Error Handling

#### Backend Error Responses

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "amount": ["The amount must be greater than 0"],
    "date": ["The date cannot be in the future"]
  }
}
```

#### Frontend Error Handling

- Display validation errors inline with form fields
- Show toast notifications for server errors
- Gracefully handle network failures
- Provide retry mechanisms

---

### Edge Cases

1. **No transactions exist**
   - Display empty state with helpful message
   - Show "Add your first transaction" prompt

2. **Category deletion with existing transactions**
   - Prevent deletion (ON DELETE RESTRICT)
   - Show error message explaining why

3. **Large transaction amounts**
   - Validate max amount (9,999,999.99)
   - Format large numbers with commas

4. **Date edge cases**
   - Prevent future dates
   - Handle timezone considerations
   - Default to current date

5. **Concurrent edits**
   - Use optimistic locking if needed
   - Show conflict resolution UI

---

## Future Enhancements

### Phase 2: Reports & Analytics

- Date range filtering
- Export to CSV/PDF
- Monthly/yearly comparisons
- Trend analysis

**Technical Dependencies:**
- Charting library enhancements
- Report generation service
- Export functionality

---

### Phase 3: AI-Powered Predictions

- Spending predictions based on historical data
- Budget recommendations
- Anomaly detection

**Technical Dependencies:**
- Machine learning integration
- Historical data analysis
- Prediction algorithms

---

### Phase 4: Behavioral Insights

- Spending pattern analysis
- Personalized recommendations
- Goal tracking

**Technical Dependencies:**
- Analytics engine
- Recommendation system
- Goal management features

---

### Phase 5: Task-Based Expense Planning

- To-do list for planned expenses
- Expense reminders
- Recurring transaction templates

**Technical Dependencies:**
- Task management system
- Notification service
- Scheduling system

---

### Phase 6: Budgeting Tools

- Budget creation and monitoring
- Budget vs actual comparisons
- Overspending alerts

**Technical Dependencies:**
- Budget management service
- Alert system
- Comparison algorithms

---

### Phase 7: Theming

- Light and dark mode
- Custom color schemes
- Accessibility themes

**Technical Dependencies:**
- Theme management system
- CSS variable architecture
- User preference storage

---

## Migration Considerations

When implementing future features:

1. **Database migrations:** Plan schema changes carefully
2. **Backward compatibility:** Ensure existing data remains valid
3. **Feature flags:** Use feature toggles for gradual rollout
4. **Data migration:** Write scripts for data transformation
5. **API versioning:** Consider API versioning strategy

---

## Appendix

### Glossary

- **Transaction:** A record of income or expense
- **Category:** A classification for transactions
- **Summary:** Calculated totals and balance
- **Net Balance:** Total income minus total expenses

### References

- See [`AGENTS.MD`](file:///c:/Users/jhonb/Documents/Websites/finance-behavioral-system/AGENTS.MD) for development standards and architecture patterns
- See [`low-concept.md`](file:///c:/Users/jhonb/Documents/Websites/finance-behavioral-system/docs/low-concept.md) for MVP overview
- See [`rule-engine.md`](file:///c:/Users/jhonb/Documents/Websites/finance-behavioral-system/docs/rule-engine.md) for pattern detection rules
- See [`finance-habit-tracker.md`](file:///c:/Users/jhonb/Documents/Websites/finance-behavioral-system/docs/finance-habit-tracker.md) for adaptive feedback system
- Laravel Documentation: https://laravel.com/docs
- Vue 3 Documentation: https://vuejs.org/guide
- TypeScript Documentation: https://www.typescriptlang.org/docs

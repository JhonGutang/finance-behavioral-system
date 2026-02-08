
> **Last Updated:** 2026-02-07
# Existing Features

This document provides a comprehensive list of currently implemented features in the Finance Behavioral System.

## 🔐 User Authentication
The system provides a secure authentication layer using Laravel Sanctum to manage user access and data privacy.

- **Account Management**:
    - **Registration**: Users can create new accounts.
    - **Login/Logout**: Secure session management.
- **Protected Access**: All financial data routes are protected by authentication middleware.
- **Profile Management**: Endpoint to retrieve the currently authenticated user's information.

## ⚙️ Rule Engine (Behavioral Logic)
The core of the system's pattern detection capabilities, providing deterministic feedback based on spending habits.

- **Deterministic Rules**: Implementation of 3 core heuristic rules:
    - **Category Overspend**: Detects >25% WoW increase in specific categories.
    - **Weekly Spending Spike**: Detects >20% WoW increase in total spending.
    - **Frequent Small Purchases**: Detects "death by a thousand cuts" (10+ transactions < ₱10).
- **Explainable Logic**: Every trigger includes supporting data (e.g., specific percentages and transaction counts).
- **Adaptive Feedback Engine**:
    - **Two-Level Feedback**: Automatically switches between **Basic** and **Advanced** advice based on user improvement.
    - **Metric Enrichment**: Calculates 4-week averages, baselines, and peak spending times for deeper insights.
- **Historical Analysis**:
    - **Progress Tracking**: Monitors rule triggers over multiple weeks to detect trends.
    - **Improvement Scoring**: Generates a 0-100 score reflecting financial habit progress.
- **Insights View**:
    - **Infinite Scrolling**: Seamlessly load historical feedback as you scroll.
    - **Data Caching**: Enhanced with TanStack Vue Query for instant access and background updates.
    - **Scroll to Top**: Quick navigation for long history lists.
- **Feedback Acknowledgment**: Mark insights as read to keep track of your progress.

## 📊 Dashboard
The Dashboard serves as the central hub for financial overview and quick insights.

- **Summary Cards**: Real-time display of:
    - **Total Income**: Sum of all recorded income transactions in ₱.
    - **Total Expenses**: Sum of all recorded expense transactions in ₱.
    - **Net Balance**: The difference between total income and total expenses in ₱.
- **Data Visualization**:
    - **Income vs Expense Bar Chart**: A visual comparison of cash flow.
    - **Expense Breakdown Pie Chart**: Distributed view of spending across categories.
    - **Historical Progress Chart**: Visualization of improvement scores and rule triggers over time.

## 💸 Transaction Management
Core functionality for tracking and managing financial events.

- **Transaction List**: A detailed table showing:
    - Date (formatted)
    - Category
    - Description
    - Amount (in ₱)
    - Type (Income/Expense)
- **Advanced Filtering**: Filter transactions by type, category, date range, and amount range.
- **Pagination**: Efficient server-side pagination (10 items per page).
- **Manual Entry**: Form to add new transactions with validation.
- **CSV Import**: Bulk-import transactions using a fixed template.
    - **Draft Review**: Preview parsed data before saving.
    - **Duplicate Detection**: Auto-flags and skips existing transactions.
    - **Auto-Categorization**: Learns and matches categories to reduce manual work.
- **User Isolation**: Users can only view, create, update, or delete their own transactions.
- **Categorization**: Transactions are associated with system or custom categories.
- **Custom Categories**: Users can create their own categories tailored to their spending habits.

## 🧭 UI / UX
Modern, responsive interface built with premium design principles.

- **Sidebar Navigation**: Fixed, collapsible sidebar with intuitive icons.
- **Responsive Design**: Fluid layout that adapts to various screen sizes.
- **Shadcn-Vue Integration**: High-quality, accessible UI components.
- **Glassmorphism & Micro-animations**: Subtle visual effects for a premium feel.
- **Theming**: Consistent color palette and typography.
- **Currency Support**: System-wide use of Philippine Peso (₱) for all displays and inputs.

## 🛠️ Internal Utilities & System
Robust technical foundation for stability and performance.

- **Date Formatter**: A specialized utility to transform date strings (e.g., `1/1/2001` → `Jan 1, 2001`) with full safety and validation.
- **Type Safety**: End-to-end TypeScript implementation for reduced runtime errors.
- **State Management**: Centralized reactive state using Pinia.
- **Clean Architecture**: Decoupled backend (Laravel) and frontend (Vue) for long-term maintainability.

## 🧪 Testing
- **Frontend Quality**: Comprehensive Jest tests for stores and utilities.
- **Backend Quality**: Robust Pest tests for API endpoints and services.
- **Regression Safety**: Strict policy against modifying working test cases.

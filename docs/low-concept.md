# Spendlytic: Finance Tracker with Adaptive Feedback Engine (MVP)

## 🚀 Project Overview

This project is an **engineering-focused MVP** that explores how deterministic feedback can help users improve financial habits over time.  
Instead of building a full budgeting app, the system focuses on:

- **tracking transactions**
- **detecting spending patterns**
- **generating adaptive feedback**
- **showing progress over time**

The goal is to demonstrate **system design, rule-based logic, and adaptive behavior**, not to create a complete financial product.

---

## ✅ MVP Scope

### Included
- Transaction input via CSV upload (or manual entry)
- Pattern detection using simple deterministic rules
- A feedback engine that generates explanations and suggestions
- Adaptive feedback that changes based on user progress
- A basic dashboard for viewing results
- Multi-user support with secure authentication (Sanctum)

### Not Included (Out of Scope)
- Bank integrations
- Machine learning or predictive modeling
- Real-time enforcement or spending blocks
- Emotional or psychological inference

---

## 🔧 Core Features

### 1. Transaction Input
Transactions are stored as events with the following fields:

- `date`
- `category`
- `amount`
- `merchant` (optional)

Data can be uploaded via CSV or entered manually.

---

### 2. Pattern Detection (3 Rules)

The system detects three key patterns:

#### Rule 1 — Category Overspend
If spending in a category exceeds the previous week by **25%**, the rule triggers.

#### Rule 2 — Weekly Spending Spike
If total weekly spending is **20% higher** than the previous week, the rule triggers.

#### Rule 3 — Frequent Small Purchases
If there are **10+ transactions under $10** in a week, the rule triggers.

---

### 3. Feedback Engine

Each rule generates feedback using templates:

- Explanation of the pattern
- Suggested action to improve
- Numeric values filled into placeholders

Example:
> “You spent **$X** in **category Y** this week, which is **Z%** higher than last week.  
> Try limiting this category to **$A** next week.”

---

### 4. Adaptive Feedback (2 Levels)

Feedback adapts based on progress:

- **If the user breaks rules 2 weeks in a row → simpler advice**
- **If the user succeeds 2 weeks in a row → more advanced advice**

This behavior is deterministic and based solely on measurable metrics.

---

## 📊 Output / Dashboard

The system displays:

- Weekly spending totals
- Pattern triggers
- Feedback history
- Progress over time

This can be implemented as:

- A CLI output + log file  
or  
- A simple web UI

---

## 🧩 What This Project Demonstrates

This MVP proves the following engineering skills:

- Event-based transaction processing
- Deterministic rule engine design
- Adaptive behavior logic
- Testable and explainable system design
- Clear scoping and trade-off awareness

---

## 🧪 Testing

The project includes:

- Unit tests for rule logic (e.g., category overspend, spending spike)
- Integration tests for end-to-end feedback generation

---

## 📌 Future Improvements

Possible next steps:

- Add more rules and patterns
- Support for bank integrations
- Add simulation and replay features

---

## 🧠 Notes

This is not a full finance app or a startup product.  
It is an engineering MVP designed to explore deterministic feedback and adaptive suggestions.


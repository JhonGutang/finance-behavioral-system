
> **Last Updated:** 2026-02-05
# Spendlytic: Finance Tracker with Adaptive Feedback Loop (Engineering Project)

## Project Motivation

Most personal finance apps focus on **observation**: tracking transactions, categorizing spending, and showing summaries. While this increases awareness, it often fails to change long-term behavior.

In real life, users continue repeating the same financial mistakes even with full visibility. This indicates the problem isn’t just a lack of information — it’s a lack of **effective feedback that guides behavior over time**.

This project explores an alternative approach:  
**treating personal finance as a system that improves through feedback, not willpower.**

The goal is not to build a full budgeting tool or predict outcomes, but to experiment with how **simple, deterministic feedback rules** can guide financial behavior over time.

---

## Project Scope

This project is intentionally scoped as an **engineering exploration**, not a production-ready finance product.

**In scope:**
- Transaction tracking and storage  
- Pattern detection using simple heuristics  
- A deterministic feedback engine  
- Adaptive feedback templates that evolve with user progress  
- Simulation and replay of feedback outcomes  

**Out of scope (by design):**
- Machine learning or predictive models  
- Emotional or psychological inference  
- Real-world transaction blocking  
- Bank integrations or live enforcement  

---

## Core Idea

Instead of only recording what happened, the system analyzes transactions and generates **feedback suggestions** based on detected patterns.

Feedback is **adaptive**: as users improve or struggle, the system changes the type of advice it gives.  
This creates a feedback loop that encourages gradual behavior improvement over time.

The project focuses on **how feedback is generated, represented, and adapted**, not on persuasion or notifications.

---

## System Overview

### 1. Transaction Tracking

- Transactions are stored as immutable events  
- Derived summaries (weekly totals, category aggregates, rolling averages) are computed from event history  
- This structure supports replay and simulation of feedback outcomes  

---

### 2. Pattern Detection (Heuristic-Based)

Patterns are identified using deterministic logic such as:

- Rolling time windows  
- Frequency thresholds  
- Category-based aggregates  
- Time-of-day and day-of-week patterns  

Examples:

- Spending in a category exceeds a rolling baseline  
- Transaction frequency spikes during specific time windows  
- Income increases followed by proportional spending increases  

These patterns are **signals**, not conclusions.

---

### 3. Feedback Engine (Core Component)

Feedback is generated from structured templates rather than hard-coded text.

Each feedback template includes:

- **Conditions** (when it should be used)  
- **Placeholders** (what values it needs)  
- **Feedback level** (basic → advanced)  
- **Suggested actions** (specific steps the user can take)  
- **Explanation template** (why it was shown)

The evaluation flow is:

1. A new transaction or summary is generated  
2. Patterns are detected  
3. Matching feedback templates are selected  
4. The system fills placeholders with computed values  
5. Feedback is emitted and stored  
6. Future feedback is adjusted based on progress  

The engine is designed to be:

- Deterministic  
- Explainable  
- Testable  
- Replayable  

---

### 4. Adaptive Feedback (Core Feature)

Feedback evolves based on user progress:

- **If the user is struggling**, the system suggests smaller, simpler actions.  
- **If the user is improving**, the system suggests more advanced optimizations.  
- **If the user is stable**, the system reinforces positive behavior and suggests incremental improvements.

This adaptive behavior is deterministic and based on measurable metrics derived from transaction history.

---

## Design Goals

- **Clarity over complexity**  
  Every feedback decision must be explainable step by step.

- **Data-driven, not speculative**  
  All feedback is derived from transaction history, not inferred psychology.

- **Extensibility without overengineering**  
  New feedback templates can be added without changing core logic.

- **Honest limitations**  
  The system does not “fix” behavior, only tests how structured feedback influences it.

---

## Why This Project

This project demonstrates:

- System design and abstraction  
- Rule-based decision modeling  
- Feedback loop engineering  
- Tradeoff awareness and scope control  
- Testing and replay of stateful systems  

Rather than building a feature-rich app, the focus is on **building one small, defensible system well** and understanding its limitations.

---

## Summary

This is not a budgeting app or a startup prototype.

It is an engineering project that explores how **deterministic feedback and adaptive suggestions**, based on financial event data, can guide behavior over time.

The emphasis is on:

- explicit logic  
- predictable outcomes  
- and thoughtful system boundaries

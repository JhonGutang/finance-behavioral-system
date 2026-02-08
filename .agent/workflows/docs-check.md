---
description: Check if documentation is synced with the system and well-managed.
---

# Documentation Check & Sync Workflow

This workflow ensures that all system documentation is up-to-date, properly listed in `AGENTS.MD`, and maintains a consistent format.

## Overview

The documentation management system tracks:
1. **Sync Status**: Ensuring every `.md` file in `/docs` is referenced in `AGENTS.MD`.
2. **Freshness**: Flagging documents that haven't been updated in over 30 days.
3. **Format**: Ensuring all documents have a "Last Updated" field.

## Workflow Steps

### 1. Check Documentation Status
Run the automated check to identify missing links or stale documents.

// turbo
```bash
node scripts/check-docs.js check
```

### 2. Sync Documentation
Automatically update `AGENTS.MD` with new files and refresh "Last Updated" timestamps for stale or unformatted documents.

// turbo
```bash
node scripts/check-docs.js sync
```

### 3. Manual Review (Agentic)
After syncing, the agent should:
- Review `docs/features.md` to ensure any new code features are described.
- Review `docs/system-overview.md` if there were architectural changes.
- Ensure `AGENTS.MD` project status and phase are still accurate.

## Slash Commands
- `/docs-check`: Run step 1 to see the current status.
- `/docs-sync`: Run step 2 to automatically fix sync issues.

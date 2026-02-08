---
description: Stage all changes, commit them, and push to the remote repository.
---

# Git Push Workflow

This workflow streamlines the process of saving and sharing your changes.

## 1. Stage Changes
// turbo
1. Add all modified and new files to the staging area.
```bash
git add .
```

## 2. Commit Changes
2. Commit the staged changes with a descriptive message.
> [!IMPORTANT]
> Change "Your commit message" to something meaningful.
```bash
git commit -m "Your commit message"
```

## 3. Sync with Remote
// turbo
3. Pull the latest changes from the remote to prevent conflicts.
```bash
git pull --rebase
```

// turbo
4. Push your local commits to the remote repository.
```bash
git push
```

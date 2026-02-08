
> **Last Updated:** 2026-02-05
# Date Formatter Utility

## Overview

A reusable date formatting utility for transforming dates from `"M/D/YYYY"` format to `"MMM D, YYYY"` format (e.g., `"1/1/2001"` → `"Jan 1, 2001"`).

## Location

- **Utility**: `src/utils/dateFormatter.ts`
- **Tests**: `src/utils/__tests__/dateFormatter.test.ts`
- **Barrel Export**: `src/utils/index.ts`

## Usage

### Basic Usage

```typescript
import { formatDate } from '@/utils';

// Transform date string
const formatted = formatDate('1/1/2001');
// Returns: "Jan 1, 2001"
```

### Safe Usage (No Errors)

```typescript
import { formatDateSafe } from '@/utils';

// Returns null instead of throwing errors for invalid dates
const formatted = formatDateSafe('1/1/2001');
// Returns: "Jan 1, 2001"

const invalid = formatDateSafe('invalid-date');
// Returns: null
```

### In Vue Components

```vue
<script setup lang="ts">
import { formatDate, formatDateSafe } from '@/utils';

const transactionDate = '12/25/2023';
const displayDate = formatDate(transactionDate);
// displayDate = "Dec 25, 2023"
</script>

<template>
  <div>
    <p>Transaction Date: {{ formatDate('1/15/2024') }}</p>
    <!-- Displays: Transaction Date: Jan 15, 2024 -->
  </div>
</template>
```

## API Reference

### `formatDate(dateString: string): string`

Transforms a date string from `"M/D/YYYY"` format to `"MMM D, YYYY"` format.

**Parameters:**
- `dateString` - Date string in `"M/D/YYYY"` format (e.g., `"1/1/2001"`)

**Returns:**
- Formatted date string in `"MMM D, YYYY"` format (e.g., `"Jan 1, 2001"`)

**Throws:**
- `Error` if the date string is invalid or cannot be parsed

**Examples:**
```typescript
formatDate('1/1/2001')      // "Jan 1, 2001"
formatDate('12/25/2023')    // "Dec 25, 2023"
formatDate('2/29/2020')     // "Feb 29, 2020" (leap year)
formatDate(' 1/1/2001 ')    // "Jan 1, 2001" (handles whitespace)
```

### `formatDateSafe(dateString: string): string | null`

Same as `formatDate`, but returns `null` instead of throwing errors for invalid input.

**Parameters:**
- `dateString` - Date string in `"M/D/YYYY"` format

**Returns:**
- Formatted date string in `"MMM D, YYYY"` format, or `null` if invalid

**Examples:**
```typescript
formatDateSafe('1/1/2001')      // "Jan 1, 2001"
formatDateSafe('invalid')       // null
formatDateSafe('13/1/2001')     // null (invalid month)
formatDateSafe('2/30/2001')     // null (invalid date)
```

## Validation

The utility performs comprehensive validation:

✅ **Month**: Must be between 1 and 12  
✅ **Day**: Must be between 1 and 31  
✅ **Year**: Must be a 4-digit year  
✅ **Calendar Date**: Validates actual calendar dates (e.g., rejects Feb 30)  
✅ **Leap Years**: Correctly handles leap year dates (e.g., Feb 29, 2020)

## Error Handling

### `formatDate` throws errors for:
- Empty or non-string input
- Invalid format (not `"M/D/YYYY"`)
- Invalid month (< 1 or > 12)
- Invalid day (< 1 or > 31)
- Invalid year (not 4 digits)
- Invalid calendar dates (e.g., Feb 30, Apr 31)

### `formatDateSafe` returns `null` for:
- All cases where `formatDate` would throw an error

## Testing

The utility has comprehensive test coverage with 16 test cases covering:
- Valid date formatting
- All 12 months
- Edge cases (leap years, whitespace)
- Error handling for invalid inputs
- Both strict and safe formatting functions

Run tests:
```bash
npm test -- dateFormatter.test.ts
```

## Architecture Alignment

This utility follows the project's architecture standards:

- ✅ **Type Safety**: Strictly typed with TypeScript
- ✅ **Code Clarity**: Descriptive names and minimal comments
- ✅ **Constants**: Month names defined as constants
- ✅ **Testing**: Comprehensive test suite
- ✅ **Location**: Placed in `utils/` directory as per architecture

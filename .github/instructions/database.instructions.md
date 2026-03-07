---
applyTo: "database/**/*.php"
---

# Database & Migration Review

## Migration Safety
- Column modifications must re-declare ALL existing attributes — omitted attributes are dropped
- Destructive migrations (dropping columns/tables) must be flagged for review
- Add indexes for columns used in WHERE, ORDER BY, or JOIN clauses
- Foreign key constraints must use `constrained()` with `cascadeOnDelete()` or `nullOnDelete()`

## Data Integrity
- Verify `$fillable` is explicitly defined on all models — flag models without mass assignment protection
- Sensitive data (passwords, tokens, secrets) must be in the model's `$hidden` array
- Verify soft deletes are used where data retention matters

## Query Performance
- Flag queries inside loops — require eager loading or batch operations
- Flag `SELECT *` patterns — specify needed columns for large tables
- Verify pagination is used for user-facing list endpoints

## Subscription & Billing Data
- Stripe-related columns and tables must never be manually modified — use Cashier's API
- Verify subscription state checks use Cashier methods, not raw column checks

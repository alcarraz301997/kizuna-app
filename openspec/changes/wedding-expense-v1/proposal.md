# Proposal: Wedding Expense v1

## Intent

Give the couple a shared web app to track wedding expenses by category and see budget status in one place, replacing spreadsheets.

## Scope

### In Scope
- Scaffold Laravel 11 + Inertia.js + React 18 project
- Couple authentication via Laravel Breeze (Inertia React)
- Expense CRUD: amount, vendor, status, notes, date, category
- Category CRUD: name, budget limit, color
- Budget overview dashboard: total budget vs actual spending

### Out of Scope
- Expense splitting, vendor directory, receipts, notifications
- Multi-currency, roles beyond shared couple access

## Capabilities

### New Capabilities
- `auth-session`: Couple registration, login, logout with Breeze Inertia React
- `category-management`: CRUD for expense categories with budget limits
- `expense-management`: CRUD for wedding expenses tied to categories
- `budget-overview`: Dashboard aggregating budget, spent, and remaining

### Modified Capabilities
- None (greenfield)

## Approach

Scaffold with Laravel Breeze's Inertia React preset. Model one shared wedding budget per couple. Categories have name, budget_limit, color. Expenses have category_id, amount, vendor, status (planned/contracted/paid), paid_date, notes. Use resource controllers returning Inertia pages. Dashboard aggregates totals and per-category progress.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `composer.json` | New | Laravel 11 + Breeze packages |
| `package.json` | New | React 18, Inertia, Vite, Tailwind |
| `app/Models/User.php` | New | Breeze user + relations |
| `app/Models/Category.php` | New | Category with budget limit |
| `app/Models/Expense.php` | New | Expense with category relation |
| `database/migrations/` | New | Users, categories, expenses, sessions |
| `app/Http/Controllers/` | New | Auth, Category, Expense, Dashboard controllers |
| `resources/js/Pages/` | New | Inertia React pages |
| `routes/web.php` | New | Auth and app routes |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Over-engineering auth | Low | Use Breeze, not Jetstream |
| Future schema churn | Med | Keep models simple and additive |
| Inertia SSR friction | Low | Standard Breeze install, no SSR |

## Rollback Plan

Delete project directory and re-run scaffolding. No production data exists, so re-scaffold is acceptable.

## Dependencies

- PHP 8.2+, Composer, Node.js 20+, npm

## Success Criteria

- [ ] Scaffolding and install complete without errors
- [ ] Registration and login work for both partners
- [ ] Category CRUD persists name, budget limit, color
- [ ] Expense CRUD persists amount, vendor, status, date, category
- [ ] Dashboard shows total budget, spent, remaining, and per-category progress
- [ ] `php artisan test && npm run test` passes

## Resolved Decisions

1. **Currency**: Single currency (no multi-currency support needed)
2. **Accounts**: Shared account — both partners use the same credentials
3. **Category budget limits**: Required — must set a limit when creating a category
4. **Payments**: Status-only (planned → contracted → paid), no partial payments or deposits in v1

## Proposal question round

Questions to refine v1 assumptions:
1. Single fixed currency, or is multi-currency needed later?
2. Shared single account or two accounts tied to one budget?
3. Should category budget limits be optional or required?
4. Is payment status sufficient, or are partial payments/deposits needed in v1?

# Design: Wedding Expense v1

## Technical Approach

Standard Laravel 11 monolith with Inertia.js React adapter. Breeze handles auth scaffolding. Resource controllers serve Inertia pages. All data scoped to the authenticated user via `user_id` foreign keys — even though v1 has a single shared account, this provides authorization structure and future multi-couple readiness. Dashboard aggregates budget data server-side and passes as Inertia props.

## Architecture Decisions

| Decision | Options | Tradeoff | Choice |
|----------|---------|----------|--------|
| Data ownership | user_id FKs vs global (no FK) | FKs add slight complexity but enable authz checks and future multi-tenancy | **user_id FKs on categories and expenses** |
| Status representation | PHP backed enum vs string constants vs DB table | Enum gives type safety, validation, and IDE support without DB overhead | **PHP 8.1 backed string enum `ExpenseStatus`** |
| Budget calculation | Model accessor vs controller query vs DB view | Accessor keeps logic on model, reusable across controllers; must eager-load sums to avoid N+1 | **Category model accessors (`spent`, `remaining`, `progress`) with controller-level eager loading** |
| Category deletion with expenses | Soft-delete vs block vs cascade | Spec requires blocking; cascade would lose data | **Block deletion, return 409 with error message** |
| Frontend state | Inertia shared data vs React state lib | Inertia props sufficient for CRUD; no client-side state lib needed | **Inertia props only, no Zustand/Redux** |
| Color storage | Hex string vs CSS color name | Hex is universal, supports color picker components | **`color` column as string(7), validated as hex** |

## Data Flow

```
Browser → Route → Middleware(auth) → Controller → Model → SQLite
                                         │
                                    Inertia::render()
                                         │
                                    React Page ← props
```

Dashboard aggregation:

```
DashboardController@__invoke
  → Category::with('expenses')->where('user_id', auth()->id())->get()
  → Per-category: spent (contracted+paid sums), planned sum, remaining
  → Totals: total_budget, total_spent, total_planned, total_remaining
  → Inertia::render('Dashboard', { categories, totals })
```

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `app/Models/Category.php` | Create | Eloquent model with `expenses` hasMany, budget accessors |
| `app/Models/Expense.php` | Create | Eloquent model with `category` belongsTo, `ExpenseStatus` cast |
| `app/Enums/ExpenseStatus.php` | Create | Backed enum: `Planned`, `Contracted`, `Paid` |
| `app/Http/Controllers/CategoryController.php` | Create | Resource controller (index, create, store, edit, update, destroy) |
| `app/Http/Controllers/ExpenseController.php` | Create | Resource controller with optional `?category_id` filter on index |
| `app/Http/Controllers/DashboardController.php` | Create | Single `__invoke` aggregating budget data |
| `database/migrations/*_create_categories_table.php` | Create | Categories table migration |
| `database/migrations/*_create_expenses_table.php` | Create | Expenses table migration |
| `resources/js/Pages/Dashboard.jsx` | Create | Budget overview: totals + per-category progress bars |
| `resources/js/Pages/Categories/Index.jsx` | Create | Category list table |
| `resources/js/Pages/Categories/Create.jsx` | Create | Category creation form |
| `resources/js/Pages/Categories/Edit.jsx` | Create | Category edit form |
| `resources/js/Pages/Expenses/Index.jsx` | Create | Expense list with category filter dropdown |
| `resources/js/Pages/Expenses/Create.jsx` | Create | Expense creation form |
| `resources/js/Pages/Expenses/Edit.jsx` | Create | Expense edit form |
| `resources/js/Layouts/AuthenticatedLayout.jsx` | Modify | Add nav links: Dashboard, Categories, Expenses |
| `routes/web.php` | Modify | Add resource routes inside auth middleware group |
| `app/Models/User.php` | Modify | Add `categories()` and `expenses()` hasMany relations |

## Interfaces / Contracts

### Database Schema

```sql
-- categories
id              INTEGER PRIMARY KEY AUTOINCREMENT
user_id         INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE
name            VARCHAR(255) NOT NULL
budget_limit    DECIMAL(10,2) NOT NULL  -- CHECK > 0
color           VARCHAR(7) NOT NULL DEFAULT '#6366f1'
created_at      TIMESTAMP
updated_at      TIMESTAMP
UNIQUE(user_id, name)

-- expenses
id              INTEGER PRIMARY KEY AUTOINCREMENT
category_id     INTEGER NOT NULL REFERENCES categories(id) ON DELETE RESTRICT
amount          DECIMAL(10,2) NOT NULL  -- CHECK > 0
vendor          VARCHAR(255) NULLABLE
status          VARCHAR(20) NOT NULL DEFAULT 'planned'
paid_date       DATE NULLABLE
notes           TEXT NULLABLE
created_at      TIMESTAMP
updated_at      TIMESTAMP
INDEX(category_id)
INDEX(status)
```

### ExpenseStatus Enum

```php
enum ExpenseStatus: string
{
    case Planned = 'planned';
    case Contracted = 'contracted';
    case Paid = 'paid';
}
```

### Route Map

| Method | URI | Controller@Method | Inertia Page |
|--------|-----|-------------------|--------------|
| GET | `/dashboard` | `DashboardController@__invoke` | `Dashboard` |
| GET | `/categories` | `CategoryController@index` | `Categories/Index` |
| GET | `/categories/create` | `CategoryController@create` | `Categories/Create` |
| POST | `/categories` | `CategoryController@store` | redirect to index |
| GET | `/categories/{category}/edit` | `CategoryController@edit` | `Categories/Edit` |
| PUT | `/categories/{category}` | `CategoryController@update` | redirect to index |
| DELETE | `/categories/{category}` | `CategoryController@destroy` | redirect to index |
| GET | `/expenses` | `ExpenseController@index` | `Expenses/Index` |
| GET | `/expenses/create` | `ExpenseController@create` | `Expenses/Create` |
| POST | `/expenses` | `ExpenseController@store` | redirect to index |
| GET | `/expenses/{expense}/edit` | `ExpenseController@edit` | `Expenses/Edit` |
| PUT | `/expenses/{expense}` | `ExpenseController@update` | redirect to index |
| DELETE | `/expenses/{expense}` | `ExpenseController@destroy` | redirect to index |

All routes inside `auth` middleware group. Category and expense controllers use route model binding with implicit scoping via `user_id`.

### Key Model Contracts

```php
// Category model
public function expenses(): HasMany;
public function getSpentAttribute(): float;      // sum of contracted+paid amounts
public function getPlannedAttribute(): float;    // sum of planned amounts
public function getRemainingAttribute(): float;  // budget_limit - spent
public function getProgressAttribute(): float;   // (spent / budget_limit) * 100

// Expense model
protected $casts = ['status' => ExpenseStatus::class, 'paid_date' => 'date', 'amount' => 'decimal:2'];
public function category(): BelongsTo;
```

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Unit (PHPUnit) | Category accessors (spent, remaining, progress), ExpenseStatus values | Model instantiation with in-memory data |
| Feature (PHPUnit) | All CRUD actions, validation rules, auth guards, category deletion blocking, expense category scoping | `Tests\TestCase` + `RefreshDatabase` + authenticated user via `actingAs` |
| Unit (Vitest) | Dashboard progress math, form validation helpers | Pure function tests |
| Integration (Vitest) | Page components render correct data from Inertia props | React Testing Library with mock props |

## Migration / Rollout

No migration required — greenfield project. Database created fresh via `php artisan migrate`. Rollback: delete project and re-scaffold.

## Open Questions

None — all product decisions were resolved in the proposal phase.

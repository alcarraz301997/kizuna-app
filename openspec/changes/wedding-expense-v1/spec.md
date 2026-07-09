# Spec: Wedding Expense v1

Greenfield change — all four capabilities are NEW (no existing `openspec/specs/` to delta against), so each section below is a full spec.

**Resolved decisions applied**: single currency; one shared couple account (shared credentials); category `budget_limit` required and positive; payment states are status-only (`planned → contracted → paid`), no partial payments.

---

## auth-session Specification

### Purpose
Give the couple access to the wedding budget through a single shared account using Laravel Breeze Inertia React.

### Requirement: Registration

The system SHALL let the couple register one shared account with name, email, password, and password confirmation.

#### Scenario: Register a shared couple account
- GIVEN no account exists yet
- WHEN the couple submits valid registration (name, email, password, confirm)
- THEN the system creates exactly one user record and authenticates the session
- AND redirects to the dashboard

#### Scenario: Duplicate email rejected
- GIVEN an account already uses email `couple@example.com`
- WHEN registration reuses that email
- THEN the system rejects with a validation error and no record is created

### Requirement: Login and Logout

The system SHALL authenticate with email + password and end the session on logout.

#### Scenario: Valid login
- GIVEN a registered couple account
- WHEN submitting correct credentials
- THEN a session starts and the couple is redirected to the dashboard

#### Scenario: Invalid credentials
- GIVEN a registered account
- WHEN submitting a wrong password
- THEN the system rejects with a credential error and no session is created

#### Scenario: Logout
- GIVEN an authenticated couple
- WHEN requesting logout
- THEN the session is invalidated and the couple is redirected to the login page

### Requirement: Authenticated Access

The system MUST deny all non-auth routes (categories, expenses, dashboard) to unauthenticated requests.

#### Scenario: Unauthenticated access
- GIVEN no active session
- WHEN requesting `/categories`, `/expenses`, or `/dashboard`
- THEN the system redirects to the login page

---

## category-management Specification

### Purpose
Define expense categories, each carrying a required positive budget limit and a color for UI display.

### Requirement: Required Category Fields

A category SHALL have `name` (unique, non-empty), `budget_limit` (required, positive numeric), and `color` (required, valid color). Single currency is fixed at the app level; no per-category currency.

#### Scenario: Create a category
- GIVEN an authenticated couple
- WHEN creating a category with name "Venue", `budget_limit` 5000, color "#7c3aed"
- THEN the category is persisted, scoped to the couple, and listed in the index

### Requirement: Budget Limit Required and Positive

A category MUST NOT be persisted when `budget_limit` is missing, zero, or negative.

#### Scenario: Budget limit omitted
- GIVEN an authenticated couple creating a category
- WHEN submitting without `budget_limit`
- THEN the system rejects with a validation error and no record is created

#### Scenario: Zero or negative limit
- GIVEN an authenticated couple
- WHEN `budget_limit` is 0 or negative
- THEN the system rejects with a validation error

### Requirement: Unique Name

Category names MUST be unique per couple account.

#### Scenario: Duplicate name
- GIVEN a category "Venue" already exists for the couple
- WHEN creating another category called "Venue"
- THEN the system rejects with a uniqueness error

### Requirement: Category CRUD

The system SHALL allow create, read, update, and delete on the couple's categories.

#### Scenario: Update a category
- GIVEN a category "Venue" with budget_limit 5000
- WHEN updating budget_limit to 6000
- THEN the category is updated and the dashboard reflects the new limit

#### Scenario: Delete a category with expenses
- GIVEN a category has linked expenses
- WHEN deleting that category
- THEN the system MUST block deletion and report that expenses must be reassigned or removed first

#### Scenario: Delete an empty category
- GIVEN a category has no expenses
- WHEN deleting it
- THEN the category is removed

---

## expense-management Specification

### Purpose
Record wedding expenses tied to a category with amount, vendor, status, date, and notes. Amounts use the app's single fixed currency.

### Requirement: Required Expense Fields

An expense SHALL have `category_id` (existing category), `amount` (positive numeric), `status` (one of `planned`, `contracted`, `paid`), and `date` (required). `vendor` and `notes` are optional.

#### Scenario: Create an expense
- GIVEN an authenticated couple with at least one category
- WHEN creating an expense with category_id, amount 1500, vendor "Floristry Co", status "planned", date 2026-09-01
- THEN the expense is persisted, scoped to the couple, and listed

#### Scenario: Amount must be positive
- GIVEN an authenticated couple
- WHEN creating an expense with amount 0 or negative
- THEN the system rejects with a validation error and no record is created

#### Scenario: Category must belong to the couple
- GIVEN another account's category exists
- WHEN creating an expense referencing that category_id
- THEN the system rejects with a not-found/authorization error

### Requirement: Status Transitions

Status MUST be one of `planned`, `contracted`, `paid`. The flow SHOULD be `planned → contracted → paid`, but direct jumps (e.g., `planned → paid`) are permitted. Partial payments and deposits are NOT supported in v1.

#### Scenario: Move planned to contracted
- GIVEN an expense with status "planned"
- WHEN updating status to "contracted"
- THEN the expense status becomes "contracted"

#### Scenario: Invalid status rejected
- GIVEN an authenticated couple
- WHEN creating/updating an expense with status "partially-paid"
- THEN the system rejects with a validation error

### Requirement: Expense CRUD

The system SHALL allow create, read, update, delete on the couple's expenses.

#### Scenario: Update an expense
- GIVEN an expense amount 1500
- WHEN updating amount to 1600 and notes to "includes delivery"
- THEN the expense is updated and dashboard totals recompute

#### Scenario: Delete an expense
- GIVEN a paid expense exists
- WHEN deleting it
- THEN the expense is removed and dashboard totals recompute

#### Scenario: List expenses by category
- GIVEN multiple expenses across categories
- WHEN the couple views the expenses list filtered by a category
- THEN only expenses in that category are returned

---

## budget-overview Specification

### Purpose
Show the couple, at a glance, the total budget, spent, remaining, and per-category progress against limits.

### Requirement: Total Aggregation

The dashboard MUST show total budget (sum of category `budget_limit`), total spent, and remaining (`budget − spent`). "Spent" = sum of expense amounts in `contracted` and `paid` statuses; `planned` expenses are shown separately as "planned".

#### Scenario: Dashboard for a populated budget
- GIVEN categories with totals 10000 and expenses: 4000 paid, 1000 contracted, 500 planned
- WHEN the couple opens the dashboard
- THEN total budget shows 10000, spent shows 5000, planned shows 500, remaining shows 5000

### Requirement: Per-Category Progress

For each category the dashboard MUST show budget limit, spent, remaining, and progress (spent / limit as a percentage).

#### Scenario: Category over budget
- GIVEN category "Venue" budget_limit 5000 with 6000 spent
- WHEN the couple opens the dashboard
- THEN "Venue" shows progress 120% and remaining −1000, flagged as over budget

#### Scenario: Category within budget
- GIVEN category "Flowers" budget_limit 1000 with 400 spent
- WHEN the couple opens the dashboard
- THEN "Flowers" shows progress 40% and remaining 600

### Requirement: Dashboard Auth Guard

The dashboard MUST require an authenticated session.

#### Scenario: Unauthenticated dashboard access
- GIVEN no active session
- WHEN requesting `/dashboard`
- THEN the system redirects to the login page

### Requirement: Recompute on Change

Aggregated totals MUST reflect the latest categories and expenses without manual refresh steps beyond a normal page load.

#### Scenario: Totals update after new expense
- GIVEN dashboard shows spent 5000
- WHEN the couple adds a contracted expense of 1000 and reloads
- THEN spent shows 6000
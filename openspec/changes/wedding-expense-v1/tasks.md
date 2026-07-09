# Tasks: Wedding Expense v1

## Review Workload Forecast

| Field | Value |
|---|---|
| Estimated changed lines | High (greenfield + Breeze artifacts) |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Suggested split | PR 1: Scaffold+Auth → PR 2: Categories → PR 3: Expenses+Dashboard |
| Delivery strategy | ask-on-risk |
| Chain strategy | stacked-to-main |

Decision needed before apply: Yes
Chained PRs recommended: Yes
Chain strategy: stacked-to-main
400-line budget risk: High

### Suggested Work Units

| Unit | Goal | PR | Depends on |
|---|---|---|---|
| 1 | Scaffold Laravel, Breeze Inertia React, SQLite, auth tests | PR 1 | — |
| 2 | Categories: migration, model, controller, pages, tests | PR 2 | Unit 1 |
| 3 | Expense CRUD, dashboard, tests, seeder | PR 3 | Unit 2 |

## Phase 1: Scaffold & Auth

- [x] 1.1 Create Laravel 11 project. (project root, —, M, artisan works)
- [x] 1.2 Install Breeze Inertia React. (`composer.json`, `package.json`, auth files, 1.1, M, Breeze present)
- [x] 1.3 Install JS deps and build. (`package-lock.json`, 1.2, S, `npm run build` exits 0)
- [x] 1.4 Configure SQLite, run migrations. (`.env`, `database/database.sqlite`, 1.2, S, migrate succeeds)
- [x] 1.5 Verify auth flows and guards. (`tests/Feature/Auth/*`, 1.4, M, auth tests pass)

## Phase 2: Domain Foundation

- [ ] 2.1 Create `ExpenseStatus` enum. (`app/Enums/ExpenseStatus.php`, 1.1, S, cases match spec)
- [ ] 2.2 Create categories migration. (`database/migrations/*_create_categories_table.php`, 1.4, S, schema matches design)
- [ ] 2.3 Create expenses migration. (`database/migrations/*_create_expenses_table.php`, 2.2, S, FK restricts delete)
- [ ] 2.4 Create `Category` model. (`app/Models/Category.php`, 2.2 2.1, M, accessors match spec)
- [ ] 2.5 Create `Expense` model. (`app/Models/Expense.php`, 2.3 2.1, S, casts work)
- [ ] 2.6 Add relations to `User`. (`app/Models/User.php`, 2.4 2.5, S, relations scoped)

## Phase 3: Category CRUD

- [ ] 3.1 Implement `CategoryController`. (`app/Http/Controllers/CategoryController.php`, 2.4 2.6, M, CRUD + delete block pass)
- [ ] 3.2 Register category routes. (`routes/web.php`, 3.1, S, routes listed)
- [ ] 3.3 Build category Inertia pages. (`resources/js/Pages/Categories/{Index,Create,Edit}.jsx`, 3.2, M, pages work)
- [ ] 3.4 Write category tests. (`tests/Feature/CategoryTest.php`, 3.1, M, pass)

## Phase 4: Expense CRUD & Dashboard

- [ ] 4.1 Implement `ExpenseController`. (`app/Http/Controllers/ExpenseController.php`, 2.5 2.6 3.1, M, CRUD + filter works)
- [ ] 4.2 Register expense routes. (`routes/web.php`, 4.1, S, routes listed)
- [ ] 4.3 Build expense Inertia pages. (`resources/js/Pages/Expenses/{Index,Create,Edit}.jsx`, 4.2 3.3, M, forms work)
- [ ] 4.4 Implement `DashboardController`. (`app/Http/Controllers/DashboardController.php`, 2.4 2.5, M, totals match spec)
- [ ] 4.5 Build `Dashboard.jsx`. (`resources/js/Pages/Dashboard.jsx`, 4.4, M, renders summary)
- [ ] 4.6 Update nav links. (`resources/js/Layouts/AuthenticatedLayout.jsx`, 3.3 4.3 4.5, S, links route)
- [ ] 4.7 Write expense and dashboard tests. (`tests/Feature/{Expense,Dashboard}Test.php`, 4.1 4.4, M, pass)

## Phase 5: Unit Tests, Seeds & Polish

- [ ] 5.1 Write unit tests. (`tests/Unit/{Category,ExpenseStatus}Test.php`, 2.4 2.1, S, unit tests pass)
- [ ] 5.2 Create factories and seeder. (`database/factories/*`, `database/seeders/DatabaseSeeder.php`, 2.4 2.5, S, seed works)
- [ ] 5.3 Run full verification. (—, all, S, `php artisan test && npm run test && npm run build` pass)

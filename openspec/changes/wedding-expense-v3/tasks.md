# Tasks: Wedding Expense v3 — Gestión de Mesas y Splitting de Gastos

## Review Workload Forecast

| Campo | Valor |
|-------|-------|
| Líneas estimadas cambiadas | ~1.500 |
| Riesgo presupuesto 400 líneas | Alto |
| Riesgo presupuesto 800 líneas (proyecto) | Alto |
| PRs encadenados recomendados | Sí |
| Split sugerido | PR 1 → PR 2 → PR 3 |
| Estrategia de entrega | ask-on-risk |
| Estrategia de cadena | stacked-to-main |

Decision needed before apply: Yes
Chained PRs recommended: Yes
Chain strategy: stacked-to-main
400-line budget risk: High

### Suggested Work Units

| # | Objetivo | PR | Comando test | Runtime harness | Rollback |
|---|----------|-----|-------------|-----------------|----------|
| 1 | Backend Mesas | PR 1 | `php artisan test --filter=TableTest` | N/A — backend sin UI | Revertir migraciones + eliminar controller |
| 2 | Guest Migración + Frontend Mesas | PR 2 | `php artisan test --filter=GuestTableTest` | Visitar /tables y /guests en navegador | Revertir migración guest + eliminar pages |
| 3 | Splitting Gastos | PR 3 | `php artisan test --filter=ExpenseSplitTest` | Crear gasto con split en UI | Revertir migración splits + eliminar componente |

## Fase 1: Infraestructura — Mesas (PR 1)

- [x] 1.1 Migración `create_tables_table` con FK user_id y unique `[user_id, name]`
- [x] 1.2 Modelo `Table` con relaciones, accessors (`occupancy_count`, `available_spots`), método `canAssignGuest()`
- [x] 1.3 `TableController` resource CRUD + destroy bloqueado si `guests()->exists()`
- [x] 1.4 Rutas `Route::resource('tables', ...)` en web.php con scoping por auth
- [x] 1.5 Tests Feature: CRUD mesas, bloqueo eliminación con invitados (TM-01/02/03)

## Fase 2: Guest → table_id + Frontend Mesas (PR 2)

- [x] 2.1 Migración `add_table_id_to_guests`: agregar FK, migrar datos desde table_number, dropear table_number
- [x] 2.2 Modificar `Guest`: reemplazar `table_number` → `table_id` en fillable, agregar `table()` belongsTo
- [x] 2.3 Modificar `GuestController`: validación table_id en store/update, pasar lista de mesas a vistas
- [x] 2.4 Modificar `User`: agregar `tables()` hasMany
- [x] 2.5 Pages Tables/{Index,Create,Edit}.jsx con ocupación visible y progreso capacidad
- [x] 2.6 Modificar Guests/{Create,Edit}.jsx: dropdown de mesas reemplaza input numérico
- [x] 2.7 Tests: asignación invitado a mesa, capacidad excedida (TM-04a/b, GR-01b)
- [x] 2.8 Tests: migración datos table_number existentes (GR-04)

## Fase 3: Splitting de Gastos (PR 3)

- [x] 3.1 Enum `SplitType` backed string (50_50, percent, fixed)
- [x] 3.2 Migración `create_expense_splits_table` con FK unique a expenses
- [x] 3.3 Modelo `ExpenseSplit` con cast split_type, relación `expense()`
- [x] 3.4 Modificar `Expense`: agregar `split()` hasOne, eager-load en controladores
- [x] 3.5 `ExpenseSplitController` store/update anidado con validación suma == amount (tolerancia 0.01)
- [x] 3.6 Rutas anidadas split en routes/web.php
- [x] 3.7 Componente `SplitForm.jsx`: selector tipo, labels editables, campos monto condicionales
- [x] 3.8 Modificar Expenses/{Create,Edit}.jsx para integrar SplitForm
- [x] 3.9 Tests Unit: cálculo split 3 tipos con data providers (ES-01a/b/c)
- [x] 3.10 Tests Feature: store/update split, validación suma inválida (ES-02, ES-03)

## Fase 4: Integración

- [ ] 4.1 Suite completa en verde: `php artisan test && npm run test`
- [ ] 4.2 Verificar exportación PDF muestra nombre de mesa (GR-03)

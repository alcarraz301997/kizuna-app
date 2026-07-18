# Diseño: Wedding Expense v3 — Gestión de Mesas y Splitting de Gastos

## Enfoque Técnico

Se introducen dos nuevas entidades (`Table`, `ExpenseSplit`) y se modifica `Guest` para reemplazar `table_number` (entero libre) por `table_id` (FK a `tables`). Se sigue el mismo patrón de scoping por usuario que `Category` y `Vendor`: cada entidad pertenece al `User` autenticado vía `user_id`. El split de gastos es 1:1 opcional con `Expense` — no genera transacciones separadas, solo documenta la intención.

## Decisiones de Arquitectura

| Decisión | Opción elegida | Alternativas | Justificación |
|---|---|---|---|
| Relación Expense-Split | `hasOne` (1:1) | `hasMany`, columna inline en expenses | 1:1 por scope del producto; `hasMany` sería sobre-ingeniería. Columna inline ensuciaría el modelo Expense. |
| Montos del split | Persistidos en BD | Calculados on-the-fly | Auditoría: si el monto del gasto cambia después, el split registrado debe preservarse inmutable. |
| Drop de table_number | Conservar columna durante migración, dropear después | Dropeo directo en la misma migración | Idempotencia: permite re-ejecutar migración sin pérdida de datos. Rollback más seguro. |
| Etiquetas del split | "Él"/"Ella" default, editables | "Persona A"/"Persona B" fijas | Producto decidió labels personalizables con defaults culturalmente relevantes. |
| Borrado de mesa | Bloqueado si tiene invitados | Borrado en cascada | Consistencia con patrón existente (Category, Vendor); previene pérdida accidental de asignaciones. |

## Flujo de Datos

```
User ──hasMany──▶ Table ──hasMany──▶ Guest
User ──hasMany──▶ Expense ──hasOne──▶ ExpenseSplit
```

- **Asignación de mesa**: GuestForm → GuestController.validate → Table.canAssign() check → Guest.table_id = table.id
- **Split**: ExpenseForm → ExpenseSplitController → calcula montos según SplitType → valida suma vs expense.amount → persiste

## Cambios en Archivos

| Archivo | Acción | Descripción |
|---|---|---|
| `app/Enums/SplitType.php` | Crear | Enum backed string: `50_50`, `percent`, `fixed` |
| `app/Models/Table.php` | Crear | `fillable: [name, capacity, user_id]`. Relaciones: `user()`, `guests()`. Accessors: `occupancy_count`, `available_spots`. Método: `canAssignGuest(): bool`. |
| `app/Models/ExpenseSplit.php` | Crear | `fillable: [expense_id, split_type, person_a_label, person_a_amount, person_b_label, person_b_amount]`. Cast `split_type` → SplitType enum. Relación: `expense()`. |
| `app/Models/Guest.php` | Modificar | Reemplazar `table_number` por `table_id` en `$fillable`. Agregar `table()` belongsTo. |
| `app/Models/Expense.php` | Modificar | Agregar `split()` hasOne. Eager-load en controlador. |
| `app/Models/User.php` | Modificar | Agregar `tables()` hasMany. |
| `database/migrations/*_create_tables_table.php` | Crear | `tables(id, user_id FK, name, capacity, timestamps)`. Unique `[user_id, name]`. |
| `database/migrations/*_add_table_id_to_guests.php` | Crear | Agregar `table_id FK nullable`, migrar `table_number` → auto-crear mesas, dropear `table_number`. |
| `database/migrations/*_create_expense_splits_table.php` | Crear | `expense_splits(id, expense_id FK unique, split_type, person_a_label, person_a_amount, person_b_label, person_b_amount, timestamps)`. |
| `app/Http/Controllers/TableController.php` | Crear | Resource CRUD. `destroy` bloqueado si `guests()->exists()`. Patrón: `authorizeTable()`. |
| `app/Http/Controllers/ExpenseSplitController.php` | Crear | `store`/`update` anidado bajo expense. Validar suma == amount. |
| `app/Http/Controllers/GuestController.php` | Modificar | `table_number` → `table_id` en validación y mapeo. Pasar lista de mesas a vistas create/edit. |
| `app/Http/Controllers/ExpenseController.php` | Modificar | Eager-load `split` en edit. Mapear split a datos para Inertia. |
| `routes/web.php` | Modificar | Agregar `Route::resource('tables', TableController::class)`. Ruta anidada para split. |
| `resources/js/Pages/Tables/{Index,Create,Edit}.jsx` | Crear | CRUD con ocupación visible en Index. |
| `resources/js/Pages/Guests/{Create,Edit}.jsx` | Modificar | Selector de mesa (dropdown) reemplaza input numérico. |
| `resources/js/Pages/Expenses/Edit.jsx` | Modificar | Sección de split (tipo, etiquetas, montos). |
| `resources/js/Components/SplitForm.jsx` | Crear | Componente reutilizable: selector de tipo, labels editables, campos de monto condicionales. |
| `resources/js/Pages/Expenses/Create.jsx` | Modificar | Agregar SplitForm al formulario de creación. |

## Interfaces / Contratos

```php
// SplitType enum
enum SplitType: string {
    case FiftyFifty = '50_50';
    case Percent = 'percent';
    case Fixed = 'fixed';
}

// Table accessor
public function getOccupancyCountAttribute(): int {
    return $this->guests()->count();
}
public function getAvailableSpotsAttribute(): int {
    return max(0, $this->capacity - $this->occupancy_count);
}
public function canAssignGuest(): bool {
    return $this->occupancy_count < $this->capacity;
}

// Validación de split (en ExpenseSplitController)
// fixed: abs((person_a_amount + person_b_amount) - expense.amount) <= 0.01
// percent: person_a = expense.amount * (percent / 100), person_b = expense.amount - person_a
// 50_50: person_a = round(expense.amount / 2, 2), person_b = expense.amount - person_a
```

## Estrategia de Testing

| Capa | Qué probar | Enfoque |
|---|---|---|
| Unit | Cálculo de split (3 tipos), accessors de Table | PHPUnit data providers para valores límite y redondeo |
| Feature | Table CRUD + bloqueo de eliminación + validación de capacidad | Laravel Feature tests siguiendo patrón `GuestTest`/`VendorTest` |
| Feature | ExpenseSplit store/update con validación de suma | `assertSessionHasErrors` para sumas inválidas |
| Feature | Guest con table_id: asignación, cambio de mesa, capacidad excedida | Tests que reflejen escenarios TM-04a, TM-04b |
| Feature | Migración de datos: table_number → table_id | Test con datos preexistentes, verificar mesas autogeneradas y FK |

## Threat Matrix

N/A — sin cambios en routing, comandos shell, subprocesos, automatización VCS/PR, clasificación de ejecutables ni integración de procesos.

## Migración / Rollout

**Migración de datos**: La migración `add_table_id_to_guests` ejecuta lógica en `up()`:
1. Agregar columna `table_id` (nullable FK).
2. Por cada `table_number` distinto no nulo en `guests`, crear `Table` con `name = "Mesa {N}"` y `capacity = COUNT(*)`.
3. Actualizar `guests.table_id` al id de la mesa correspondiente.
4. Dropear columna `table_number`.

**Rollback**: `down()` reestablece `table_number` desde snapshot en tabla temporal (o simplemente revierte creando la columna de nuevo si los datos ya migraron — la migración es idempotente).

## Preguntas Abiertas

- Ninguna. Todas las decisiones de producto están resueltas en la propuesta.

# Propuesta: Wedding Expense v3 — Gestión de Mesas y Splitting de Gastos

## Intent

V3 agrega dos capacidades a la app de gastos de boda:
1. **Gestión de Mesas**: convertir `table_number` libre (v2) en una entidad `Table` con su propio CRUD y validación de capacidad. Hoy el número de mesa es texto libre sin coherencia ni control de ocupación.
2. **Splitting de Gastos**: registrar qué porción del monto asume cada miembro de la pareja (50/50, porcentajes custom o montos fijos). La cuenta es compartida (un solo usuario), por lo que el split es conceptual — documentan la intención, no generan transacciones separadas.

## Scope

### In Scope
- CRUD de mesas: `name` (obligatorio), `capacity` (entero positivo).
- Asignación de invitados a mesas existentes (reemplaza `table_number` libre).
- Validación: no asignar más invitados que `capacity` de la mesa.
- Listado de mesas con invitados asignados y ocupación.
- Splitting de gasto: guardar `split_type` (`50_50`, `percent`, `fixed`) y dos valores (parte A / parte B).
- Vista del split en el detalle del gasto.
- Validación: partes deben sumar el monto total del gasto.
- Migración de datos: `table_number` existentes → mesas autogeneradas (si hay invitados asignados).

### Out of Scope
- Drag-and-drop en vista de mesas (futuro).
- Split con cuentas separadas / multi-usuario (futuro).
- Geo/distribución visual de la sala.
- Pagos parciales reales por persona.
- Split con más de 2 personas.

## Capabilities

> Contrato entre propuesta y specs. `sdd-spec` crea/actualiza specs según esta sección.

### New Capabilities
- `table-management`: CRUD de mesas y asignación validada de invitados a mesas.
- `expense-splitting`: registro y visualización de cómo un gasto se divide entre los dos miembros de la pareja.

### Modified Capabilities
- `guest-rsvp`: `table_number` (entero libre) se reemplaza por FK `table_id` (nullable a `tables`). La exportación PDF y el listado muestran nombre de mesa en vez de número libre.
- `expense-management`: el gasto PUEDE tener un `split` asociado (1:1 opcional). El monto total del gasto no cambia.

## Approach

**Mesas**: nueva tabla `tables` (id, couple_id, name, capacity). FK `guests.table_id` nullable reemplaza `table_number`. Validación de capacidad en capa de servicio antes de persistir asignación. Política de eliminación: bloquear si tiene invitados asignados (igual que categorías/proveedores). Migración: para cada `table_number` distinto existente, crear mesa autogenerada "Mesa N" con capacidad = cantidad de invitados asignados (mínimo configurable por defecto).

**Splitting**: nueva tabla `expense_splits` (id, expense_id, split_type, person_a_label, person_a_amount, person_b_label, person_b_amount). Relación 1:1 con `expenses`. Validaciones: sumar `person_a_amount + person_b_amount == expense.amount` (con tolerancia de redondeo a 2 decimales). Para `50_50` y `percent`, los montos se calculan automáticamente. Para `fixed`, se ingresan a mano y se valida la suma. Etiquetas default: "Él" / "Ella" (editables).

## Affected Areas

| Area | Impact | Descripción |
|------|--------|-------------|
| `app/Models/Table.php` | New | Modelo Table con relación a invitados. |
| `app/Models/Guest.php` | Modified | Reemplaza `table_number` por `table_id` FK. |
| `app/Models/Expense.php` | Modified | Relación `split()` 1:1. |
| `app/Models/ExpenseSplit.php` | New | Modelo del split. |
| `database/migrations/*` | New | Tablas `tables` y `expense_splits`, alter `guests`. |
| `app/Http/Controllers/TableController.php` | New | CRUD de mesas. |
| `app/Http/Controllers/ExpenseSplitController.php` | New | Store/update del split (anidado a expense). |
| `app/Http/Controllers/GuestController.php` | Modified | Cambio de campo `table_number` → `table_id`. |
| `resources/js/Pages/Tables/*` | New | Vistas-index/create/show. |
| `resources/js/Pages/Expenses/Show.tsx` | Modified | Sección de split. |
| `database/seeders`/migración de datos | New | Migrar `table_number` existentes a mesas. |
| `tests/Feature/*` | New/Modified | Cobertura de mesas, split y migración. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Migración de `table_number` con datos inconsistentes (nulls, duplicados) | Med | Script idempotente; null → sin asignación; duplicados → una mesa por número distinto. |
| Redondeo en splits porcentuales (ej: 100 / 3) | Alta | Validar con tolerancia 0.01; redondear a 2 decimales en la capa de cálculo. |
| Eliminación de mesa con invitados asignados rompe integridad | Baja | Bloquear borrado, igual que categorías/proveedores. |
| Confusión de usuario: split vs pago real | Media | UX claro: "intención de pago", no transacción. Texto explícito en UI. |
| Cambio breaking en `guest-rsvp` para datos v2 | Media | Migración + tests de retrocompat del listado/PDF. |

## Rollback Plan

Migraciones `down`:
- `down` de `expense_splits`: dropear tabla (no afecta gastos existentes).
- `down` de `tables`: reestablecer `guests.table_number` desde snapshot pre-migración y dropear `tables` y FK.
- Restaurar `GuestController`, vistas y modelo a estado v2.
- Revert seeders.
Stored data de `table_number` se preserva en columna durante la migración (no se dropea hasta confirmar estabilidad).

## Dependencies

- Laravel migrations anidadas (orden: `tables` antes que alter `guests`).
- Sin nuevas dependencias externas de JS (se reutilizan componentes shadcn/ui existentes).

## Decisiones Resueltas

1. **Etiquetas del split**: "Él" / "Ella" por defecto, editables por el usuario.
2. **Migración de mesas**: Crear automáticamente una mesa "Mesa N" por cada `table_number` existente, con capacidad = cantidad de invitados asignados actualmente.
3. **Capacidad de mesa**: Bloquear asignación si se excede la capacidad (error claro).

## Success Criteria

- [ ] CRUD de mesas funciona con validación de capacidad.
- [ ] Asignar invitado a mesa llena es rechazado con error claro.
- [ ] Invitados migrados desde v2 conservan su asignación (mesas autogeneradas).
- [ ] Split 50/50, por porcentaje y por monto fijo se calculan y validan correctamente.
- [ ] Detalle del gasto muestra el split cuando existe.
- [ ] Tests Feature + Unit cubren mesas, splits, migración y validaciones.
- [ ] Suite existente (154 tests) sigue en verde.
- [ ] Sin breakings para datos v2 correctamente migrados.
# Especificaciones — Wedding Expense v3

Especificaciones para `wedding-expense-v3`: 2 nuevas capacidades + 2 modificadas.

---

## table-management (Nuevo)

### Propósito
CRUD de mesas con validación de capacidad. Reemplaza `table_number` libre de v2.

### Requisitos

| # | Requisito | DEBE |
|---|-----------|------|
| TM-01 | CRUD de mesas: `name` (obligatorio, único por pareja), `capacity` (entero positivo). Scope: cuenta de la pareja. | Crear, leer, actualizar, eliminar. |
| TM-02 | Bloquear eliminación si la mesa tiene invitados asignados. | Rechazar con error claro. |
| TM-03 | Listado con ocupación: nombre, capacidad, N invitados, N/capacidad. | Mostrar ocupación en tiempo real. |
| TM-04 | Validación de capacidad al asignar invitado: rechazar si ocupación >= capacidad. | Error: "La mesa '{nombre}' está llena (N/{capacidad})". |

### Escenarios

| # | GIVEN | WHEN | THEN |
|---|-------|------|------|
| TM-01a | Pareja autenticada | Crea mesa "Principal", capacidad 10 | Persiste, visible en listado |
| TM-01b | Mesa "Principal" existe | Crea otra "Principal" | Rechazado: nombre duplicado |
| TM-02a | Mesa con 3 invitados | Elimina mesa | Bloqueado: "Existen invitados asignados" |
| TM-02b | Mesa sin invitados | Elimina mesa | Mesa removida |
| TM-03 | Mesas con distinta ocupación | Ve listado | "Principal (5/10)", "Jardín (10/10 — llena)" |
| TM-04a | Mesa cap. 10, 7 ocupados | Asigna invitado | Asignado, ocupación 8/10 |
| TM-04b | Mesa cap. 10, 10 ocupados | Asigna invitado | Rechazado: "llena (10/10)" |

---

## expense-splitting (Nuevo)

### Propósito
Registrar división conceptual del gasto entre los dos miembros de la pareja. Split 1:1 opcional con cada gasto. No genera transacciones separadas.

### Requisitos

| # | Requisito | DEBE |
|---|-----------|------|
| ES-01 | Split types: `50_50` (auto), `percent` (porcentajes suman 100), `fixed` (montos manuales). Etiquetas default "Él"/"Ella", editables. | Soportar los 3 tipos. |
| ES-02 | Validación: `person_a + person_b == expense.amount` (tolerancia 0.01). | Rechazar si no suma. |
| ES-03 | Detalle del gasto muestra sección de split cuando existe. Sin split → no se muestra. | Mostrar etiquetas, montos, tipo. |

### Escenarios

| # | GIVEN | WHEN | THEN |
|---|-------|------|------|
| ES-01a | Gasto de 1000 | Split `50_50` | A=500, B=500 |
| ES-01b | Gasto de 1000 | Split `percent`: 60/40 | A=600, B=400 |
| ES-01c | Gasto de 1000 | Split `fixed`: 700/300 | Persiste ok |
| ES-01d | Split con "Él"/"Ella" | Edita a "Juan"/"María" | Etiquetas actualizadas |
| ES-02 | Gasto de 1000 | Split `fixed`: 600/300 (suma 900) | Rechazado: "no suman el total" |
| ES-03 | Gasto sin split | Ve detalle | Sin sección de split |

---

## guest-rsvp (Modificado)

### MODIFIED Requirements

#### GR-01 — CRUD de invitados

El sistema DEBE crear, leer, actualizar y eliminar invitados. Campos: `name` (obligatorio), `email` (opcional, formato email), `phone` (opcional), `rsvp_status` (obligatorio, enum: `pendiente`, `confirmado`, `no_asiste`), `table_id` (opcional, FK nullable a `tables`). Scope: cuenta de la pareja.

(Previamente: `table_number` era entero libre sin validación ni entidad de mesa.)

| # | GIVEN | WHEN | THEN |
|---|-------|------|------|
| GR-01a | Pareja autenticada | Crea "María López", pendiente, sin mesa | Persiste con table_id null |
| GR-01b | Mesa "Principal" (cap. 10, 5 ocupados), invitado sin mesa | Asigna a mesa "Principal" | table_id → mesa, ocupación 6/10 |
| GR-01c | Invitado "pendiente" | Actualiza a "confirmado" | Contador se actualiza |

#### GR-03 — Exportación PDF

El sistema DEBE exportar PDF con nombre, email, teléfono, RSVP y **nombre de mesa** (no número).

(Previamente: PDF mostraba `table_number` como entero libre.)

| # | GIVEN | WHEN | THEN |
|---|-------|------|------|
| GR-03 | Invitados con mesa asignada | Exporta PDF | Muestra "Principal", "Jardín" — no números |

### ADDED Requirements

#### GR-04 — Migración de mesas existentes

El sistema DEBE migrar `table_number` existentes no nulos a mesas autogeneradas. Por cada número distinto, crear "Mesa N" con capacidad = cantidad de invitados con ese número. Invitados reasignados vía `table_id`.

| # | GIVEN | WHEN | THEN |
|---|-------|------|------|
| GR-04 | Invitados con table_number 1 (3) y 2 (5) | Ejecuta migración | "Mesa 1" (cap. 3), "Mesa 2" (cap. 5), invitados reasignados |

---

## expense-management (Modificado)

### MODIFIED Requirements

#### Required Expense Fields

Un gasto DEBE tener `category_id` (existente), `amount` (positivo), `status` (`planned`, `contracted`, `paid`) y `date` (obligatorio). `vendor`, `notes`: opcionales. **v2:** `vendor_id` FK nullable a `vendors`. **v3:** split opcional (relación 1:1 con `expense_splits`). El monto total del gasto no cambia al agregar/quitar split.

(Previamente: no existía concepto de split.)

| # | GIVEN | WHEN | THEN |
|---|-------|------|------|
| RE-01 | Pareja con categoría | Crea gasto amount 2000 + split 50_50 | Gasto 2000, split A=1000/B=1000 |
| RE-02 | Pareja con categoría | Crea gasto sin split | Persiste normal, sin registro en splits |
| RE-03 | Pareja | Crea gasto amount 0 | Rechazado: monto debe ser positivo |
| RE-04 | Categoría de otra cuenta | Crea gasto con ese category_id | Rechazado: no autorizado |

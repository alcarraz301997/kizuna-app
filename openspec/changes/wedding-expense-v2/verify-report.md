# Reporte de Verificación: Wedding Expense v2

## Resumen Ejecutivo

| Campo | Valor |
|-------|-------|
| Change | `wedding-expense-v2` |
| Modo | Standard verify (full artifacts: proposal + specs + design + tasks) |
| Branch | `feat/wedding-v2-integration` |
| Veredicto | **PASS** (corregido) |
| Fecha | 2026-07-18 |
| Fix Branch | `fix/v2-verify-issues` |
| Fix Fecha | 2026-07-18 |

### Resultado de Ejecución (Post-Fix)

| Comando | Exit Code | Resultado |
|---------|-----------|-----------|
| `php artisan test` | 0 | 142 passed (592 assertions) — 2.83s |
| `npm run test` | 0 | 12 passed (3 test files) — 1.23s |
| `npm run build` | 0 | 1056 modules, built in 2.62s |

---

## Tabla de Completitud de Tareas

| PR | Tareas | Completadas | Estado |
|----|--------|-------------|--------|
| PR 1: Proveedores | 1.1 – 1.13 | 13/13 | ✅ |
| PR 2: Recibos + Gastos | 2.1 – 2.11 | 11/11 | ✅ |
| PR 3: Invitados + PDF | 3.1 – 3.8 + adicional | 10/10 | ✅ |
| PR 4: Integración | 4.1 – 4.4 | 4/4 | ✅ |
| **Total** | **38 subtareas** | **38/38** | **✅** |

Todas las tareas marcadas completadas en `tasks.md`.

---

## Matriz de Cumplimiento de Specs

### vendor-directory (3 requisitos, 4 escenarios)

| Req/Escenario | Estado | Evidencia |
|---------------|--------|-----------|
| VD-01: CRUD de proveedores | ✅ PASS | `VendorController` resource completo; `VendorTest` CRUD verificado |
| VD-01a: Crear proveedor | ✅ PASS | `test_vendor_can_be_created` — persiste y redirige a index |
| VD-01b: Editar estado de pago | ✅ PASS | `test_vendor_can_be_updated` — actualiza a `pagado_completo` |
| VD-02: Eliminación bloqueada con gastos | ✅ PASS | `test_vendor_deletion_blocked_when_expenses_exist` — vendor permanece en BD |
| VD-03: Filtro por categoría | ✅ PASS | `test_vendor_index_filters_by_service_category` — filtra correctamente |

### receipt-uploads (3 requisitos, 5 escenarios)

| Req/Escenario | Estado | Evidencia |
|---------------|--------|-----------|
| RC-01: Subida de adjuntos | ✅ PASS | `test_receipt_can_be_uploaded` + `test_receipt_image_can_be_uploaded` |
| RC-01a: Subir recibo PDF | ✅ PASS | PDF 2MB se guarda en storage y BD |
| RC-01b: Límite de 5 alcanzado | ✅ PASS | `test_receipt_limit_of_five_is_enforced` — rechaza con mensaje |
| RC-02: Validación de tipo y tamaño | ✅ PASS | `test_receipt_rejects_invalid_mime_type` + `test_receipt_rejects_file_exceeding_max_size` |
| RC-02a: Tipo no permitido | ✅ PASS | `.exe` rechazado con error de validación MIME |
| RC-02b: Tamaño excedido | ✅ PASS | 11MB rechazado con error de validación |
| RC-03: Eliminar adjunto | ✅ PASS | `test_receipt_can_be_deleted` — borra archivo + registro BD |

### guest-rsvp (3 requisitos, 5 escenarios)

| Req/Escenario | Estado | Evidencia |
|---------------|--------|-----------|
| GR-01: CRUD de invitados | ✅ PASS | `GuestController` resource completo; tests de create/update/delete |
| GR-01a: Crear invitado | ✅ PASS | `test_guest_can_be_created` — persiste con table_number null |
| GR-01b: Asignar mesa | ✅ PASS | `test_guest_can_be_created_with_table_number` — mesa = 3 |
| GR-01c: Cambiar RSVP | ✅ PASS | `test_guest_rsvp_can_be_updated` — pendiente → confirmado |
| GR-02: Contador | ✅ PASS | `test_rsvp_counter_shows_correct_counts` — total=5, confirmados=2, pendientes=3 (total - confirmados) |
| GR-03: Exportar PDF | ✅ PASS | `test_guest_pdf_export_generates_download` — PDF descargable |

### expense-management Delta (1 requisito modificado, 4 escenarios)

| Req/Escenario | Estado | Evidencia |
|---------------|--------|-----------|
| vendor_id FK nullable | ✅ PASS | Migración `add_vendor_id_to_expenses` con `nullOnDelete()` |
| Crear gasto con vendor_id | ✅ PASS | `test_expense_can_be_created_with_vendor_id` |
| Crear gasto con vendor texto (fallback v1) | ✅ PASS | `test_expense_falls_back_to_text_vendor` |
| Amount must be positive | ✅ PASS | `test_expense_requires_positive_amount` + `test_expense_rejects_negative_amount` |
| Category must belong to couple | ✅ PASS | `test_expense_rejects_other_users_category` — 403 |

---

## Tabla de Coherencia de Diseño

| Decisión de Diseño | Implementación | Estado |
|--------------------|----------------|--------|
| Receipt como modelo Eloquent independiente | `app/Models/Receipt.php` con `belongsTo expense` | ✅ |
| Backed string enums | `VendorPaymentStatus`, `RsvpStatus` — ambos `enum: string` | ✅ |
| Bloquear eliminación de vendor con gastos | `VendorController@destroy` — redirect con flash error | ✅ |
| Disco `receipts` dedicado | `config/filesystems.php` — disco `receipts` en `storage/app/receipts` con `serve => true` | ✅ |
| Sanitización: `time() . '_' . Str::random(8)` | `ReceiptController@store` línea 41 | ✅ |
| Scope `user_id` en tablas nuevas | vendors, receipts, guests — todas con FK `user_id` | ✅ |
| dompdf para export PDF | `GuestController@export` usa `Barryvdh\DomPDF\Facade\Pdf` | ✅ |
| NavLinks en AuthenticatedLayout | Proveedores + Invitados agregados (desktop + mobile) | ✅ |

---

## Hallazgos — Todos Resueltos

### CRITICAL (1) — RESUELTO

#### C1: Contador RSVP no cumple scenario GR-02 ✅ RESUELTO

**Fix aplicado en `fix/v2-verify-issues`**:
- `GuestController@index`: Cambiado a `'pendientes' => $total - $confirmados` (total - confirmados).
- `GuestTest::test_rsvp_counter_shows_correct_counts`: Assert cambiado a `->where('counts.pendientes', 3)`.
- `Guests/Index.jsx`: Label actualizado a "Pendientes (no confirmados)" para claridad.

### WARNING (4) — TODOS RESUELTOS

#### W1: Disco `receipts` sin `serve => true` ✅ RESUELTO

**Fix**: Agregado `'serve' => true` y `'url' => .../'/storage/receipts'` al disco `receipts` en `config/filesystems.php`. La URL única evita conflicto con el disco `local`.

#### W2: Create.jsx sube archivos que son ignorados silenciosamente ✅ RESUELTO

**Fix**: `ExpenseController@store` ahora procesa archivos del campo `receipt_files`. Después de crear el gasto, itera los archivos subidos y crea registros `Receipt` con las mismas reglas de validación (máx 5, solo tipos permitidos) que `ReceiptController`. Los archivos se guardan en el disco `receipts` bajo `{expense_id}/`.

#### W3: Método `show` ausente en controladores resource ✅ RESUELTO

**Fix**: Agregado método `show()` a `VendorController` y `GuestController`. Ambos redirigen a la ruta `index` correspondiente. Las URLs `/vendors/{id}` y `/guests/{id}` ahora redirigen en lugar de causar error 500.

#### W4: Ruta de storage de recibos no sigue estructura spec ✅ RESUELTO

**Fix**: `ReceiptController@store` ahora usa `$file->storeAs((string) $expense->id, $filename, 'receipts')` en lugar de `storeAs('/', ...)`. Los archivos se organizan por `{expense_id}/` subdirectorio.

---

### SUGGESTION (3)

#### S1: Sin directorio `lang/` para localización

No existe directorio `lang/es/`. Todos los strings están hardcodeados en español en controladores, componentes React y templates Blade. Funcional para una app solo-español, pero no sigue el patrón de localización de Laravel.

#### S2: Unicidad de nombre de vendor por usuario (más allá del spec)

La migración de vendors agrega `unique(['user_id', 'name'])`. El spec no requiere esta restricción. Es buena práctica (sigue el patrón de categorías) pero es una extensión no documentada.

#### S3: Ruta `guests/export/pdf` definida después del resource

**Archivo**: `routes/web.php`, línea 38

La ruta específica `GET guests/export/pdf` se define después de `Route::resource('guests', ...)`. Funciona correctamente (diferente número de segmentos), pero la convención de Laravel es definir rutas específicas antes del resource para evitar conflictos potenciales.

---

## Evidencia de Ejecución

### Backend Tests (142 passed, 592 assertions)

```
Tests\Unit\CategoryTest         ✓ 8 tests
Tests\Unit\ExampleTest          ✓ 1 test
Tests\Unit\ExpenseStatusTest    ✓ 7 tests
Tests\Unit\RsvpStatusTest       ✓ 7 tests
Tests\Feature\Auth\*            ✓ 17 tests
Tests\Feature\CategoryTest      ✓ 17 tests
Tests\Feature\DashboardTest     ✓ 7 tests
Tests\Feature\ExampleTest       ✓ 1 test
Tests\Feature\ExpenseTest       ✓ 21 tests
Tests\Feature\GuestTest         ✓ 21 tests
Tests\Feature\ProfileTest       ✓ 5 tests
Tests\Feature\ReceiptTest       ✓ 10 tests
Tests\Feature\VendorTest        ✓ 18 tests
```

### Frontend Tests (12 passed)

```
resources/js/tests/utils/formatCurrency.test.js     ✓ 6 tests
resources/js/tests/components/TextInput.test.jsx     ✓ 3 tests
resources/js/tests/components/PrimaryButton.test.jsx ✓ 3 tests
```

### Build (successful)

```
vite v5.4.21 — 1059 modules transformed
36 assets generated — built in 2.59s
```

---

## Verificación de Localización

| Área | Estado | Notas |
|------|--------|-------|
| Controladores (validación) | ✅ Español | Mensajes custom en español en `ReceiptController` |
| Controladores (flash) | ✅ Español | "Existen gastos vinculados...", "Máximo 5 adjuntos..." |
| Páginas React (labels) | ✅ Español | "Proveedores", "Invitados", "Crear Gasto", etc. |
| Páginas React (placeholders) | ✅ Español | "S/. 0.00", "Ej: Florería Local" |
| Template PDF | ✅ Español | "Lista de Invitados - Boda", headers, estados |
| Seeders (demo data) | ✅ Español | Nombres, categorías, notas en español |
| NavLinks | ✅ Español | "Panel", "Categorías", "Gastos", "Proveedores", "Invitados" |

---

## Veredicto Final: **PASS** ✅

**Post-Fix**: Los 5 hallazgos (1 CRITICAL + 4 WARNING) fueron corregidos en el branch `fix/v2-verify-issues`. Todos los tests pasan (142 backend, 12 frontend) y el build es exitoso.

### Archivos modificados

| Archivo | Cambio |
|---------|--------|
| `app/Http/Controllers/GuestController.php` | C1: Contador `pendientes = total - confirmados`; W3: Método `show()` agregado |
| `app/Http/Controllers/VendorController.php` | W3: Método `show()` agregado |
| `app/Http/Controllers/ExpenseController.php` | W2: Procesamiento de archivos `receipt_files` en `store()` |
| `app/Http/Controllers/ReceiptController.php` | W4: Path de storage `{expense_id}/` en vez de raíz plana |
| `config/filesystems.php` | W1: `serve => true` + `url` único para disco `receipts` |
| `resources/js/Pages/Guests/Index.jsx` | C1: Label "Pendientes (no confirmados)" |
| `tests/Feature/GuestTest.php` | C1: Assert `counts.pendientes = 3` |

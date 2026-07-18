# Tareas: Wedding Expense v2

## Revisión de Carga de Trabajo

| Campo | Valor |
|-------|-------|
| Líneas estimadas cambiadas | ~1500-1700 |
| Riesgo presupuesto de revisión (800 líneas) | Alto |
| PRs encadenados recomendados | Sí |
| Split sugerido | PR 1: Proveedores → PR 2: Recibos → PR 3: Invitados → PR 4: Integración |
| Estrategia de entrega | ask-on-risk |
| Estrategia de cadena | feature-branch-chain |

Decision needed before apply: Yes
Chained PRs recommended: Yes
Chain strategy: feature-branch-chain
400-line budget risk: High

### Unidades de Trabajo Sugeridas

| # | Objetivo | PR | Comando test enfocado | Runtime harness | Rollback boundary |
|---|----------|----|----------------------|-----------------|-------------------|
| 1 | Enums + migrations + CRUD Proveedores | PR 1 | `php artisan test --filter=Vendor` | N/A — asserts HTTP puros | Revert migrations vendors + add_vendor_id |
| 2 | Recibos adjuntos + modificación gastos | PR 2 | `php artisan test --filter=Receipt` | Subir/ver/eliminar recibo en detalle gasto | Revert migration receipts + storage receipts/ |
| 3 | CRUD Invitados + export PDF | PR 3 | `php artisan test --filter=Guest` | N/A — backend + dompdf sin UI compleja | Revert migration guests |
| 4 | NavLinks + seeders + suite completa | PR 4 | `php artisan test && npm run test` | `npm run build` + navegación manual | Revert NavLinks (solo layout/html) |

## PR 1: Proveedores (Vendor Domain)

- [x] 1.1 Crear `app/Enums/VendorPaymentStatus.php` (backed string: no_iniciado, pagado_parcialmente, pagado_completo)
- [x] 1.2 Migration `create_vendors_table` (FK user_id, name, service_category, payment_status, contact email/phone, notes)
- [x] 1.3 Migration `add_vendor_id_to_expenses` (FK vendor_id nullable ON DELETE SET NULL)
- [x] 1.4 Modelo `app/Models/Vendor.php` (hasMany expenses, cast payment_status a enum)
- [x] 1.5 Modificar `app/Models/Expense.php` (+vendor_id fillable, belongsTo vendor, hasMany receipts)
- [x] 1.6 Modificar `app/Models/User.php` (+vendors hasMany)
- [x] 1.7 Agregar disco `receipts` en `config/filesystems.php` (local, root: storage/app/receipts)
- [x] 1.8 Instalar `barryvdh/laravel-dompdf` vía composer
- [x] 1.9 Controlador `VendorController` (resource + index con filtro `?service_category`)
- [x] 1.10 Ruta `Route::resource('vendors', VendorController::class)` en `routes/web.php`
- [x] 1.11 Página `Vendors/Index.jsx` (listado + filtro por categoría de servicio)
- [x] 1.12 Páginas `Vendors/Create.jsx` + `Vendors/Edit.jsx` (formulario con selects)
- [x] 1.13 Tests: `tests/Feature/VendorTest.php` (CRUD + bloqueo eliminación con gastos vinculados - scenario VD-02)

## PR 2: Recibos y Modificación de Gastos (Receipts + Expense Mods)

- [x] 2.1 Migration `create_receipts_table` (FK expense_id + user_id, file_path/name/type/size)
- [x] 2.2 Modelo `app/Models/Receipt.php` (belongsTo expense, accessor file_url con Storage::url)
- [x] 2.3 Controlador `ReceiptController` (store anidado expenses/{expense}/receipts + destroy)
- [x] 2.4 Rutas receipts en `routes/web.php` (POST + DELETE, límite 5 adjuntos validado en controller)
- [x] 2.5 Modificar `ExpenseController@store` (+vendor_id opcional con validación de pertenencia al user)
- [x] 2.6 Modificar `ExpenseController@update` (+vendor_id opcional)
- [x] 2.7 Modificar `ExpenseController@edit` (+lista de vendors para selector)
- [x] 2.8 Componente `ReceiptPreview.jsx` (vista previa imagen / nombre PDF + botón eliminar)
- [x] 2.9 Modificar `Expenses/Create.jsx` (+selector vendor + subida múltiple archivos)
- [x] 2.10 Modificar `Expenses/Edit.jsx` (+selector vendor + galería adjuntos existentes)
- [x] 2.11 Tests: `tests/Feature/ReceiptTest.php` (RC-01a subida, RC-01b límite 5, RC-02a MIME, RC-02b tamaño, RC-03 eliminación)

## PR 3: Invitados y Export PDF (Guest Domain)

- [x] 3.1 Crear `app/Enums/RsvpStatus.php` (backed string: pendiente, confirmado, no_asiste)
- [x] 3.2 Migration `create_guests_table` (FK user_id, name, email, phone, rsvp_status, table_number nullable)
- [x] 3.3 Modelo `app/Models/Guest.php` (belongsTo user, cast rsvp_status)
- [x] 3.4 Controlador `GuestController` (resource + export() con dompdf)
- [x] 3.5 Rutas guests + ruta `GET /guests/export` en `routes/web.php`
- [x] 3.6 Página `Guests/Index.jsx` (listado + contador "Confirmados: N / Pendientes: M" + botón export PDF)
- [x] 3.7 Páginas `Guests/Create.jsx` + `Guests/Edit.jsx` (formulario con selects RSVP + campo mesa)
- [x] 3.8 Tests: `tests/Feature/GuestTest.php` (GR-01a crear, GR-01b asignar mesa, GR-01c cambiar RSVP, GR-02 contador, GR-03 export PDF)

### Adicional
- [x] Test `tests/Unit/RsvpStatusTest.php` (7 enum unit tests)
- [x] Factory `database/factories/GuestFactory.php`

## PR 4: Integración y Verificación

- [ ] 4.1 Modificar `AuthenticatedLayout.jsx` (NavLinks: Proveedores → `/vendors` ✓, Invitados → `/guests` pendiente)
- [ ] 4.2 Factories: `database/factories/VendorFactory.php` ✓ + `GuestFactory.php` pendiente
- [ ] 4.3 Actualizar `DatabaseSeeder.php` (datos demo de proveedores e invitados)
- [ ] 4.4 Suite completa: `php artisan test && npm run test && npm run build`

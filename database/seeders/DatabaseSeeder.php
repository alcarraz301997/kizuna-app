<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Guest;
use App\Models\Receipt;
use App\Models\User;
use App\Models\Vendor;
use App\Enums\VendorPaymentStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $categories = [
            ['name' => 'Venue', 'budget_limit' => 8000, 'color' => '#7c3aed'],
            ['name' => 'Catering', 'budget_limit' => 5000, 'color' => '#2563eb'],
            ['name' => 'Photography', 'budget_limit' => 3000, 'color' => '#059669'],
            ['name' => 'Flowers', 'budget_limit' => 1500, 'color' => '#dc2626'],
            ['name' => 'Music & DJ', 'budget_limit' => 2000, 'color' => '#d97706'],
            ['name' => 'Attire', 'budget_limit' => 2500, 'color' => '#ec4899'],
            ['name' => 'Invitations', 'budget_limit' => 500, 'color' => '#8b5cf6'],
            ['name' => 'Transportation', 'budget_limit' => 1000, 'color' => '#0891b2'],
        ];

        foreach ($categories as $catData) {
            $category = Category::factory()->create(array_merge($catData, [
                'user_id' => $user->id,
            ]));

            // Create 2-4 expenses per category with varied statuses
            $expenseCount = rand(2, 4);
            for ($i = 0; $i < $expenseCount; $i++) {
                Expense::factory()->create([
                    'category_id' => $category->id,
                    'user_id' => $user->id,
                ]);
            }
        }

        // ──────────────────────────────────
        // v2: Vendor demo data (5-8 vendors)
        // ──────────────────────────────────
        $vendors = [
            [
                'name' => 'Hacienda San José',
                'service_category' => 'Venue',
                'contact_phone' => '987654321',
                'contact_email' => 'reservas@haciendasanjose.com',
                'payment_status' => VendorPaymentStatus::PagadoCompleto->value,
                'notes' => 'Salón principal con jardín. Capacidad: 200 personas.',
            ],
            [
                'name' => 'Sabores del Perú Catering',
                'service_category' => 'Catering',
                'contact_phone' => '976543210',
                'contact_email' => 'eventos@saboresdelperu.pe',
                'payment_status' => VendorPaymentStatus::PagadoParcialmente->value,
                'notes' => 'Menú de 3 tiempos. Incluye barra libre y torta nupcial.',
            ],
            [
                'name' => 'Carlos Rojas Fotografía',
                'service_category' => 'Fotografía',
                'contact_phone' => '965432109',
                'contact_email' => 'carlos@rojafoto.com',
                'payment_status' => VendorPaymentStatus::PagadoCompleto->value,
                'notes' => 'Cobertura 8 horas + álbum digital + video highlight.',
            ],
            [
                'name' => 'Armonía Floral',
                'service_category' => 'Flores',
                'contact_phone' => '954321098',
                'contact_email' => null,
                'payment_status' => VendorPaymentStatus::NoIniciado->value,
                'notes' => 'Ramo de novia, centros de mesa y arco floral.',
            ],
            [
                'name' => 'DJ Sonora',
                'service_category' => 'Música',
                'contact_phone' => '943210987',
                'contact_email' => 'contrataciones@djsonora.pe',
                'payment_status' => VendorPaymentStatus::PagadoCompleto->value,
                'notes' => 'DJ + saxofonista en vivo. Equipo de sonido incluido.',
            ],
            [
                'name' => 'Decoraciones Elegancia',
                'service_category' => 'Decoración',
                'contact_phone' => '932109876',
                'contact_email' => 'info@decoracioneselegancia.com',
                'payment_status' => VendorPaymentStatus::PagadoParcialmente->value,
                'notes' => 'Mobiliario, iluminación y mantelería premium.',
            ],
            [
                'name' => 'Transportes Ejecutivos',
                'service_category' => 'Transporte',
                'contact_phone' => '921098765',
                'contact_email' => null,
                'payment_status' => VendorPaymentStatus::NoIniciado->value,
                'notes' => 'Auto clásico para los novios. Bus para 40 invitados.',
            ],
        ];

        foreach ($vendors as $vendorData) {
            Vendor::create(array_merge($vendorData, ['user_id' => $user->id]));
        }

        // ──────────────────────────────────
        // v2: Guest demo data (10-15 guests)
        // ──────────────────────────────────
        $guests = [
            ['name' => 'María López García', 'email' => 'maria.lopez@email.com', 'phone' => '987111222', 'rsvp_status' => 'confirmado', 'table_number' => 1],
            ['name' => 'Juan Pérez Torres', 'email' => 'juan.perez@email.com', 'phone' => '987222333', 'rsvp_status' => 'confirmado', 'table_number' => 1],
            ['name' => 'Ana Castillo Ruiz', 'email' => null, 'phone' => '987333444', 'rsvp_status' => 'confirmado', 'table_number' => 2],
            ['name' => 'Pedro Mendoza Silva', 'email' => 'pedro.mendoza@email.com', 'phone' => null, 'rsvp_status' => 'confirmado', 'table_number' => 2],
            ['name' => 'Carmen Vargas Díaz', 'email' => 'carmen.vargas@email.com', 'phone' => '987444555', 'rsvp_status' => 'pendiente', 'table_number' => null],
            ['name' => 'Roberto Quispe Huamán', 'email' => null, 'phone' => '987555666', 'rsvp_status' => 'pendiente', 'table_number' => null],
            ['name' => 'Lucía Fernández Ríos', 'email' => 'lucia.fernandez@email.com', 'phone' => '987666777', 'rsvp_status' => 'confirmado', 'table_number' => 3],
            ['name' => 'Jorge Ramírez Paredes', 'email' => 'jorge.ramirez@email.com', 'phone' => null, 'rsvp_status' => 'confirmado', 'table_number' => 3],
            ['name' => 'Sofía Torres Vega', 'email' => null, 'phone' => '987777888', 'rsvp_status' => 'pendiente', 'table_number' => null],
            ['name' => 'Daniel Herrera Campos', 'email' => 'daniel.herrera@email.com', 'phone' => '987888999', 'rsvp_status' => 'no_asiste', 'table_number' => null],
            ['name' => 'Valentina Guzmán León', 'email' => 'valentina.guzman@email.com', 'phone' => null, 'rsvp_status' => 'confirmado', 'table_number' => 4],
            ['name' => 'Mateo Campos Ortega', 'email' => null, 'phone' => '987999000', 'rsvp_status' => 'pendiente', 'table_number' => null],
        ];

        foreach ($guests as $guestData) {
            Guest::create(array_merge($guestData, ['user_id' => $user->id]));
        }

        // ──────────────────────────────────
        // v2: Receipt demo data (2-3 receipts)
        // ──────────────────────────────────
        $expenses = Expense::where('user_id', $user->id)->take(3)->get();
        foreach ($expenses as $index => $expense) {
            if ($index >= 3) break;
            Receipt::create([
                'expense_id' => $expense->id,
                'user_id' => $user->id,
                'file_path' => 'receipts/demo/recibo_' . ($index + 1) . '.pdf',
                'file_name' => 'recibo_' . ($index + 1) . '.pdf',
                'file_type' => 'application/pdf',
                'file_size' => rand(50000, 500000),
            ]);
        }
    }
}

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import DangerButton from '@/Components/DangerButton';
import PrimaryButton from '@/Components/PrimaryButton';
import ResponsiveTable from '@/Components/ResponsiveTable';
import { formatCurrency } from '@/utils/formatCurrency';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

const statusColors = {
    planned: 'bg-amber-100 text-amber-800',
    contracted: 'bg-sky-100 text-sky-800',
    paid: 'bg-emerald-100 text-emerald-800',
};

const statusLabels = {
    planned: 'Planeado',
    contracted: 'Contratado',
    paid: 'Pagado',
};

export default function Index({ wedding, expenses, categories, filters }) {
    const flash = usePage().props.flash;
    const { delete: destroy, processing } = useForm();

    const handleDelete = (id) => {
        if (confirm('¿Estás seguro de que deseas eliminar este gasto?')) {
            destroy(route('weddings.expenses.destroy', [wedding.id, id]));
        }
    };

    const columns = [
        {
            key: 'vendor',
            label: 'Proveedor',
            render: (row) => (
                <span className="text-gray-900">{row.vendor || '—'}</span>
            ),
        },
        {
            key: 'category',
            label: 'Categoría',
            render: (row) => (
                <div className="flex items-center">
                    <span
                        className="mr-2 inline-block h-3 w-3 rounded-full shrink-0"
                        style={{ backgroundColor: row.category?.color }}
                    />
                    <span className="text-gray-700">{row.category?.name}</span>
                </div>
            ),
        },
        {
            key: 'amount',
            label: 'Monto',
            render: (row) => (
                <span className="font-medium text-gray-900">{formatCurrency(row.amount)}</span>
            ),
        },
        {
            key: 'status',
            label: 'Estado',
            render: (row) => (
                <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${statusColors[row.status] || ''}`}>
                    {statusLabels[row.status] || row.status}
                </span>
            ),
        },
        {
            key: 'date',
            label: 'Fecha',
            render: (row) => (
                <span className="text-gray-500">{row.paid_date || row.created_at}</span>
            ),
        },
    ];

    const rowActions = (row) => (
        <>
            <Link
                href={route('weddings.expenses.edit', [wedding.id, row.id])}
                className="clay-btn clay-btn-secondary !px-3 !py-1.5 text-xs font-semibold text-primary"
            >
                Editar
            </Link>
            <DangerButton
                disabled={processing}
                onClick={() => handleDelete(row.id)}
            >
                Eliminar
            </DangerButton>
        </>
    );

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Gastos
                    </h2>
                    <Link href={route('weddings.expenses.create', wedding.id)}>
                        <PrimaryButton>Agregar Gasto</PrimaryButton>
                    </Link>
                </div>
            }
        >
            <Head title="Gastos" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {flash?.error && (
                        <div className="clay-card clay-card-danger mb-4 p-4">
                            <p className="text-sm text-red-700">{flash.error}</p>
                        </div>
                    )}

                    {flash?.success && (
                        <div className="clay-card clay-card-success mb-4 p-4">
                            <p className="text-sm text-green-700">{flash.success}</p>
                        </div>
                    )}

                    <div className="mb-4">
                        <select
                            className="clay-select px-4 py-2.5 text-sm text-gray-700 min-h-touch"
                            value={filters.category_id || ''}
                            onChange={(e) => {
                                const params = new URLSearchParams(window.location.search);
                                if (e.target.value) {
                                    params.set('category_id', e.target.value);
                                } else {
                                    params.delete('category_id');
                                }
                                const qs = params.toString();
                                window.location.href = route('weddings.expenses.index', wedding.id) + (qs ? '?' + qs : '');
                            }}
                        >
                            <option value="">Todas las categorías</option>
                            {categories.map((cat) => (
                                <option key={cat.id} value={cat.id}>
                                    {cat.name}
                                </option>
                            ))}
                        </select>
                    </div>

                    <ResponsiveTable
                        columns={columns}
                        rows={expenses}
                        rowKey={(row) => row.id}
                        actions={rowActions}
                        emptyMessage="Aún no hay gastos."
                        emptyLinkHref={route('weddings.expenses.create', wedding.id)}
                        emptyLinkText="Crea uno"
                    />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

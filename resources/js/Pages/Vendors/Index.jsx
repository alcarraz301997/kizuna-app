import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import DangerButton from '@/Components/DangerButton';
import PrimaryButton from '@/Components/PrimaryButton';
import ResponsiveTable from '@/Components/ResponsiveTable';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

const paymentStatusLabels = {
    no_iniciado: 'No iniciado',
    pagado_parcialmente: 'Pagado parcialmente',
    pagado_completo: 'Pagado completo',
};

const paymentStatusColors = {
    no_iniciado: 'bg-gray-100 text-gray-800',
    pagado_parcialmente: 'bg-amber-100 text-amber-800',
    pagado_completo: 'bg-emerald-100 text-emerald-800',
};

export default function Index({ vendors, serviceCategories, filters }) {
    const flash = usePage().props.flash;
    const { delete: destroy, processing, get } = useForm();

    const handleDelete = (id) => {
        if (confirm('¿Estás seguro de que deseas eliminar este proveedor?')) {
            destroy(route('vendors.destroy', id));
        }
    };

    const handleFilter = (category) => {
        get(route('vendors.index', { service_category: category || null }), {
            preserveState: true,
        });
    };

    const columns = [
        {
            key: 'name',
            label: 'Nombre',
            render: (row) => (
                <div className="flex items-center">
                    <span className="font-medium text-gray-900">{row.name}</span>
                    {row.expenses_count > 0 && (
                        <span className="clay-icon clay-icon-sky ml-2 !rounded-full !px-2 !py-0.5 text-xs font-semibold text-sky-700">
                            {row.expenses_count}
                        </span>
                    )}
                </div>
            ),
        },
        {
            key: 'service_category',
            label: 'Categoría',
            render: (row) => (
                <span className="text-gray-700">{row.service_category}</span>
            ),
        },
        {
            key: 'contact',
            label: 'Contacto',
            render: (row) => (
                <div className="text-sm text-gray-700">
                    {row.contact_email && (
                        <div>{row.contact_email}</div>
                    )}
                    {row.contact_phone && (
                        <div className="text-gray-500">{row.contact_phone}</div>
                    )}
                    {!row.contact_email && !row.contact_phone && (
                        <span className="text-gray-400">—</span>
                    )}
                </div>
            ),
        },
        {
            key: 'payment_status',
            label: 'Estado de pago',
            render: (row) => (
                <span className={`inline-block rounded-full px-2.5 py-1 text-xs font-semibold ${paymentStatusColors[row.payment_status] || 'bg-gray-100 text-gray-800'}`}>
                    {paymentStatusLabels[row.payment_status]}
                </span>
            ),
        },
    ];

    const rowActions = (row) => (
        <>
            <Link
                href={route('vendors.edit', row.id)}
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
                        Proveedores
                    </h2>
                    <Link href={route('vendors.create')}>
                        <PrimaryButton>Agregar Proveedor</PrimaryButton>
                    </Link>
                </div>
            }
        >
            <Head title="Proveedores" />

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

                    {serviceCategories.length > 0 && (
                        <div className="mb-4 flex flex-wrap gap-2">
                            <button
                                onClick={() => handleFilter('')}
                                className={`clay-btn !rounded-full !px-4 !py-1.5 text-sm font-medium ${
                                    !filters.service_category
                                        ? 'clay-btn-primary'
                                        : 'clay-btn-secondary'
                                }`}
                            >
                                Todas
                            </button>
                            {serviceCategories.map((cat) => (
                                <button
                                    key={cat}
                                    onClick={() => handleFilter(cat)}
                                    className={`clay-btn !rounded-full !px-4 !py-1.5 text-sm font-medium ${
                                        filters.service_category === cat
                                            ? 'clay-btn-primary'
                                            : 'clay-btn-secondary'
                                    }`}
                                >
                                    {cat}
                                </button>
                            ))}
                        </div>
                    )}

                    <ResponsiveTable
                        columns={columns}
                        rows={vendors}
                        rowKey={(row) => row.id}
                        actions={rowActions}
                        emptyMessage="Aún no hay proveedores."
                        emptyLinkHref={route('vendors.create')}
                        emptyLinkText="Crea uno"
                    />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

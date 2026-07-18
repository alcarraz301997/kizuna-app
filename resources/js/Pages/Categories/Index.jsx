import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import DangerButton from '@/Components/DangerButton';
import PrimaryButton from '@/Components/PrimaryButton';
import ResponsiveTable from '@/Components/ResponsiveTable';
import { formatCurrency } from '@/utils/formatCurrency';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

export default function Index({ categories }) {
    const flash = usePage().props.flash;
    const { delete: destroy, processing } = useForm();

    const handleDelete = (id) => {
        if (confirm('¿Estás seguro de que deseas eliminar esta categoría?')) {
            destroy(route('categories.destroy', id));
        }
    };

    const columns = [
        {
            key: 'name',
            label: 'Categoría',
            render: (row) => (
                <div className="flex items-center">
                    <span
                        className="mr-3 inline-block h-4 w-4 rounded-full shrink-0"
                        style={{ backgroundColor: row.color }}
                    />
                    <span className="font-medium text-gray-900">
                        {row.name}
                    </span>
                </div>
            ),
        },
        {
            key: 'budget_limit',
            label: 'Presupuesto',
            render: (row) => (
                <span className="text-gray-900">{formatCurrency(row.budget_limit)}</span>
            ),
        },
        {
            key: 'spent',
            label: 'Gastado',
            render: (row) => (
                <span className="text-gray-900">{formatCurrency(row.spent)}</span>
            ),
        },
        {
            key: 'remaining',
            label: 'Restante',
            render: (row) => (
                <span className={row.remaining < 0 ? 'font-semibold text-red-600' : 'text-gray-900'}>
                    {formatCurrency(row.remaining)}
                </span>
            ),
        },
    ];

    const rowActions = (row) => (
        <>
            <Link
                href={route('categories.edit', row.id)}
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
                        Categorías
                    </h2>
                    <Link href={route('categories.create')}>
                        <PrimaryButton>Agregar Categoría</PrimaryButton>
                    </Link>
                </div>
            }
        >
            <Head title="Categorías" />

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

                    <ResponsiveTable
                        columns={columns}
                        rows={categories}
                        rowKey={(row) => row.id}
                        actions={rowActions}
                        emptyMessage="Aún no hay categorías."
                        emptyLinkHref={route('categories.create')}
                        emptyLinkText="Crea una"
                    />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

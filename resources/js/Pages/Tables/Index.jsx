import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import DangerButton from '@/Components/DangerButton';
import PrimaryButton from '@/Components/PrimaryButton';
import ResponsiveTable from '@/Components/ResponsiveTable';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

export default function Index({ wedding, tables }) {
    const { delete: destroy, processing } = useForm();
    const { props } = usePage();

    const handleDelete = (id) => {
        if (confirm('¿Estás seguro de que deseas eliminar esta mesa?')) {
            destroy(route('weddings.tables.destroy', [wedding.id, id]));
        }
    };

    const columns = [
        {
            key: 'name',
            label: 'Nombre',
            render: (row) => (
                <span className="font-medium text-gray-900">{row.name}</span>
            ),
        },
        {
            key: 'capacity',
            label: 'Capacidad',
            render: (row) => (
                <span className="text-gray-700">{row.capacity}</span>
            ),
        },
        {
            key: 'occupation',
            label: 'Ocupación',
            render: (row) => {
                const isFull = row.guests_count >= row.capacity;
                const pct = row.capacity > 0
                    ? Math.round((row.guests_count / row.capacity) * 100)
                    : 0;
                return (
                    <div className="flex items-center gap-2">
                        <div className="clay-input h-2 w-24 overflow-hidden !shadow-none">
                            <div
                                className={`h-full rounded-full transition-all ${
                                    isFull
                                        ? 'bg-red-400'
                                        : pct > 75
                                            ? 'bg-amber-400'
                                            : 'bg-emerald-400'
                                }`}
                                style={{ width: `${Math.min(pct, 100)}%` }}
                            />
                        </div>
                        <span className={`text-sm ${isFull ? 'font-semibold text-red-600' : 'text-gray-600'}`}>
                            {row.guests_count}/{row.capacity}
                            {isFull && ' — llena'}
                        </span>
                    </div>
                );
            },
        },
    ];

    const rowActions = (row) => (
        <>
            <Link
                href={route('weddings.tables.edit', [wedding.id, row.id])}
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
                        Mesas
                    </h2>
                    <Link href={route('weddings.tables.create', wedding.id)}>
                        <PrimaryButton>Crear Mesa</PrimaryButton>
                    </Link>
                </div>
            }
        >
            <Head title="Mesas" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {props.flash?.error && (
                        <div className="clay-card clay-card-danger mb-4 p-4 text-sm text-red-700">
                            {props.flash.error}
                        </div>
                    )}
                    {props.flash?.success && (
                        <div className="clay-card clay-card-success mb-4 p-4 text-sm text-green-700">
                            {props.flash.success}
                        </div>
                    )}

                    <ResponsiveTable
                        columns={columns}
                        rows={tables}
                        rowKey={(row) => row.id}
                        actions={rowActions}
                        emptyMessage="Aún no hay mesas."
                        emptyLinkHref={route('weddings.tables.create', wedding.id)}
                        emptyLinkText="Crea una"
                    />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

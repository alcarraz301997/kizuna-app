import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import DangerButton from '@/Components/DangerButton';
import PrimaryButton from '@/Components/PrimaryButton';
import ResponsiveTable from '@/Components/ResponsiveTable';
import { Head, Link, useForm } from '@inertiajs/react';

const rsvpStatusLabels = {
    pendiente: 'Pendiente',
    confirmado: 'Confirmado',
    no_asiste: 'No Asiste',
};

const rsvpStatusColors = {
    pendiente: 'bg-amber-100 text-amber-800',
    confirmado: 'bg-emerald-100 text-emerald-800',
    no_asiste: 'bg-rose-100 text-rose-800',
};

export default function Index({ wedding, guests, counts }) {
    const { delete: destroy, processing } = useForm();

    const handleDelete = (id) => {
        if (confirm('¿Estás seguro de que deseas eliminar este invitado?')) {
            destroy(route('weddings.guests.destroy', [wedding.id, id]));
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
            key: 'email',
            label: 'Email',
            render: (row) => (
                <span className="text-gray-700">{row.email || '—'}</span>
            ),
        },
        {
            key: 'phone',
            label: 'Teléfono',
            render: (row) => (
                <span className="text-gray-700">{row.phone || '—'}</span>
            ),
        },
        {
            key: 'rsvp_status',
            label: 'Estado RSVP',
            render: (row) => (
                <span className={`inline-block rounded-full px-2.5 py-1 text-xs font-semibold ${rsvpStatusColors[row.rsvp_status] || 'bg-gray-100 text-gray-800'}`}>
                    {rsvpStatusLabels[row.rsvp_status]}
                </span>
            ),
        },
        {
            key: 'table_name',
            label: 'Mesa',
            render: (row) => (
                <span className="text-gray-700">{row.table_name ?? '—'}</span>
            ),
        },
    ];

    const rowActions = (row) => (
        <>
            <Link
                href={route('weddings.guests.edit', [wedding.id, row.id])}
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
                        Invitados
                    </h2>
                    <div className="flex items-center gap-2">
                        <a
                            href={route('weddings.guests.export.pdf', wedding.id)}
                            className="clay-btn clay-btn-success !rounded-xl px-5 py-2.5 text-xs font-semibold uppercase tracking-widest"
                        >
                            Exportar PDF
                        </a>
                        <Link href={route('weddings.guests.create', wedding.id)}>
                            <PrimaryButton>Agregar Invitado</PrimaryButton>
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title="Invitados" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div className="clay-card clay-card-indigo p-5">
                            <p className="text-sm font-medium text-primary">Total</p>
                            <p className="text-2xl font-bold text-gray-900">{counts.total}</p>
                        </div>
                        <div className="clay-card clay-card-emerald p-5">
                            <p className="text-sm font-medium text-success">Confirmados</p>
                            <p className="text-2xl font-bold text-gray-900">{counts.confirmados}</p>
                        </div>
                        <div className="clay-card clay-card-amber p-5">
                            <p className="text-sm font-medium text-warning">Pendientes (no confirmados)</p>
                            <p className="text-2xl font-bold text-gray-900">{counts.pendientes}</p>
                        </div>
                    </div>

                    <ResponsiveTable
                        columns={columns}
                        rows={guests}
                        rowKey={(row) => row.id}
                        actions={rowActions}
                        emptyMessage="Aún no hay invitados."
                        emptyLinkHref={route('weddings.guests.create', wedding.id)}
                        emptyLinkText="Agrega uno"
                    />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

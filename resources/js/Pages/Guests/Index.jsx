import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import DangerButton from '@/Components/DangerButton';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, useForm } from '@inertiajs/react';

const rsvpStatusLabels = {
    pendiente: 'Pendiente',
    confirmado: 'Confirmado',
    no_asiste: 'No Asiste',
};

const rsvpStatusColors = {
    pendiente: 'bg-yellow-100 text-yellow-800',
    confirmado: 'bg-green-100 text-green-800',
    no_asiste: 'bg-red-100 text-red-800',
};

export default function Index({ guests, counts }) {
    const { delete: destroy, processing } = useForm();

    const handleDelete = (id) => {
        if (confirm('¿Estás seguro de que deseas eliminar este invitado?')) {
            destroy(route('guests.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Invitados
                    </h2>
                    <div className="flex items-center gap-2">
                        <a
                            href={route('guests.export.pdf')}
                            className="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
                        >
                            Exportar PDF
                        </a>
                        <Link href={route('guests.create')}>
                            <PrimaryButton>Agregar Invitado</PrimaryButton>
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title="Invitados" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div className="rounded-lg bg-white p-4 shadow">
                            <p className="text-sm text-gray-500">Total</p>
                            <p className="text-2xl font-bold text-gray-900">{counts.total}</p>
                        </div>
                        <div className="rounded-lg bg-white p-4 shadow">
                            <p className="text-sm text-green-600">Confirmados</p>
                            <p className="text-2xl font-bold text-green-700">{counts.confirmados}</p>
                        </div>
                        <div className="rounded-lg bg-white p-4 shadow">
                            <p className="text-sm text-yellow-600">Pendientes (no confirmados)</p>
                            <p className="text-2xl font-bold text-yellow-700">{counts.pendientes}</p>
                        </div>
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {guests.length === 0 ? (
                                <p className="text-center text-gray-500">
                                    Aún no hay invitados.{' '}
                                    <Link
                                        href={route('guests.create')}
                                        className="text-indigo-600 underline hover:text-indigo-900"
                                    >
                                        Agrega uno
                                    </Link>
                                    .
                                </p>
                            ) : (
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Nombre
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Email
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Teléfono
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Estado RSVP
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Mesa
                                            </th>
                                            <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Acciones
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200">
                                        {guests.map((guest) => (
                                            <tr key={guest.id}>
                                                <td className="whitespace-nowrap px-6 py-4 font-medium text-gray-900">
                                                    {guest.name}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-gray-700">
                                                    {guest.email || '—'}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-gray-700">
                                                    {guest.phone || '—'}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span
                                                        className={`inline-block rounded-full px-2 py-1 text-xs font-medium ${
                                                            rsvpStatusColors[
                                                                guest.rsvp_status
                                                            ] || 'bg-gray-100 text-gray-800'
                                                        }`}
                                                    >
                                                        {
                                                            rsvpStatusLabels[
                                                                guest.rsvp_status
                                                            ]
                                                        }
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-gray-700">
                                                    {guest.table_number ?? '—'}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        <Link
                                                            href={route(
                                                                'guests.edit',
                                                                guest.id,
                                                            )}
                                                            className="text-sm text-indigo-600 hover:text-indigo-900"
                                                        >
                                                            Editar
                                                        </Link>
                                                        <DangerButton
                                                            disabled={processing}
                                                            onClick={() =>
                                                                handleDelete(guest.id)
                                                            }
                                                        >
                                                            Eliminar
                                                        </DangerButton>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import DangerButton from '@/Components/DangerButton';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

export default function Index({ tables }) {
    const { delete: destroy, processing } = useForm();
    const { props } = usePage();

    const handleDelete = (id) => {
        if (confirm('¿Estás seguro de que deseas eliminar esta mesa?')) {
            destroy(route('tables.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Mesas
                    </h2>
                    <Link href={route('tables.create')}>
                        <PrimaryButton>Crear Mesa</PrimaryButton>
                    </Link>
                </div>
            }
        >
            <Head title="Mesas" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Flash messages */}
                    {props.flash?.error && (
                        <div className="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
                            {props.flash.error}
                        </div>
                    )}
                    {props.flash?.success && (
                        <div className="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                            {props.flash.success}
                        </div>
                    )}

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {tables.length === 0 ? (
                                <p className="text-center text-gray-500">
                                    Aún no hay mesas.{' '}
                                    <Link
                                        href={route('tables.create')}
                                        className="text-indigo-600 underline hover:text-indigo-900"
                                    >
                                        Crea una
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
                                                Capacidad
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Ocupación
                                            </th>
                                            <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Acciones
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200">
                                        {tables.map((table) => {
                                            const isFull = table.guests_count >= table.capacity;
                                            const pct = table.capacity > 0
                                                ? Math.round((table.guests_count / table.capacity) * 100)
                                                : 0;

                                            return (
                                                <tr key={table.id}>
                                                    <td className="whitespace-nowrap px-6 py-4 font-medium text-gray-900">
                                                        {table.name}
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-gray-700">
                                                        {table.capacity}
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4">
                                                        <div className="flex items-center gap-2">
                                                            <div className="h-2 w-24 overflow-hidden rounded-full bg-gray-200">
                                                                <div
                                                                    className={`h-full rounded-full transition-all ${
                                                                        isFull
                                                                            ? 'bg-red-500'
                                                                            : pct > 75
                                                                              ? 'bg-yellow-500'
                                                                              : 'bg-green-500'
                                                                    }`}
                                                                    style={{ width: `${Math.min(pct, 100)}%` }}
                                                                />
                                                            </div>
                                                            <span
                                                                className={`text-sm ${
                                                                    isFull
                                                                        ? 'text-red-600'
                                                                        : 'text-gray-600'
                                                                }`}
                                                            >
                                                                {table.guests_count}/{table.capacity}
                                                                {isFull && ' — llena'}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-right">
                                                        <div className="flex items-center justify-end gap-2">
                                                            <Link
                                                                href={route('tables.edit', table.id)}
                                                                className="text-sm text-indigo-600 hover:text-indigo-900"
                                                            >
                                                                Editar
                                                            </Link>
                                                            <DangerButton
                                                                disabled={processing}
                                                                onClick={() => handleDelete(table.id)}
                                                            >
                                                                Eliminar
                                                            </DangerButton>
                                                        </div>
                                                    </td>
                                                </tr>
                                            );
                                        })}
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

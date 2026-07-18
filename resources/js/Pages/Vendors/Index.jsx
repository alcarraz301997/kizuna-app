import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import DangerButton from '@/Components/DangerButton';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

const paymentStatusLabels = {
    no_iniciado: 'No iniciado',
    pagado_parcialmente: 'Pagado parcialmente',
    pagado_completo: 'Pagado completo',
};

const paymentStatusColors = {
    no_iniciado: 'bg-gray-100 text-gray-800',
    pagado_parcialmente: 'bg-yellow-100 text-yellow-800',
    pagado_completo: 'bg-green-100 text-green-800',
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

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
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
                        <div className="mb-4 rounded-md bg-red-50 p-4">
                            <p className="text-sm text-red-700">{flash.error}</p>
                        </div>
                    )}

                    {serviceCategories.length > 0 && (
                        <div className="mb-4 flex flex-wrap gap-2">
                            <button
                                onClick={() => handleFilter('')}
                                className={`rounded-full px-3 py-1 text-sm font-medium ${
                                    !filters.service_category
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                }`}
                            >
                                Todas
                            </button>
                            {serviceCategories.map((cat) => (
                                <button
                                    key={cat}
                                    onClick={() => handleFilter(cat)}
                                    className={`rounded-full px-3 py-1 text-sm font-medium ${
                                        filters.service_category === cat
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                    }`}
                                >
                                    {cat}
                                </button>
                            ))}
                        </div>
                    )}

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {vendors.length === 0 ? (
                                <p className="text-center text-gray-500">
                                    Aún no hay proveedores.{' '}
                                    <Link
                                        href={route('vendors.create')}
                                        className="text-indigo-600 underline hover:text-indigo-900"
                                    >
                                        Crea uno
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
                                                Categoría
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Contacto
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Estado de pago
                                            </th>
                                            <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Acciones
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200">
                                        {vendors.map((vendor) => (
                                            <tr key={vendor.id}>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <div className="flex items-center">
                                                        <span className="font-medium text-gray-900">
                                                            {vendor.name}
                                                        </span>
                                                        {vendor.expenses_count > 0 && (
                                                            <span className="ml-2 rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700">
                                                                {vendor.expenses_count}
                                                            </span>
                                                        )}
                                                    </div>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-gray-700">
                                                    {vendor.service_category}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <div className="text-sm text-gray-700">
                                                        {vendor.contact_email && (
                                                            <div>{vendor.contact_email}</div>
                                                        )}
                                                        {vendor.contact_phone && (
                                                            <div className="text-gray-500">
                                                                {vendor.contact_phone}
                                                            </div>
                                                        )}
                                                        {!vendor.contact_email &&
                                                            !vendor.contact_phone && (
                                                                <span className="text-gray-400">
                                                                    —
                                                                </span>
                                                            )}
                                                    </div>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span
                                                        className={`inline-block rounded-full px-2 py-1 text-xs font-medium ${
                                                            paymentStatusColors[
                                                                vendor.payment_status
                                                            ] || 'bg-gray-100 text-gray-800'
                                                        }`}
                                                    >
                                                        {
                                                            paymentStatusLabels[
                                                                vendor.payment_status
                                                            ]
                                                        }
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        <Link
                                                            href={route(
                                                                'vendors.edit',
                                                                vendor.id,
                                                            )}
                                                            className="text-sm text-indigo-600 hover:text-indigo-900"
                                                        >
                                                            Editar
                                                        </Link>
                                                        <DangerButton
                                                            disabled={processing}
                                                            onClick={() =>
                                                                handleDelete(vendor.id)
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

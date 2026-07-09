import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import DangerButton from '@/Components/DangerButton';
import PrimaryButton from '@/Components/PrimaryButton';
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

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
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
                        <div className="mb-4 rounded-md bg-red-50 p-4">
                            <p className="text-sm text-red-700">{flash.error}</p>
                        </div>
                    )}

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {categories.length === 0 ? (
                                <p className="text-center text-gray-500">
                                    Aún no hay categorías.{' '}
                                    <Link
                                        href={route('categories.create')}
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
                                                Categoría
                                            </th>
                                            <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Presupuesto
                                            </th>
                                            <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Gastado
                                            </th>
                                            <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Restante
                                            </th>
                                            <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Acciones
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200">
                                        {categories.map((category) => (
                                            <tr key={category.id}>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <div className="flex items-center">
                                                        <span
                                                            className="mr-3 inline-block h-4 w-4 rounded-full"
                                                            style={{ backgroundColor: category.color }}
                                                        />
                                                        <span className="font-medium text-gray-900">
                                                            {category.name}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-right text-gray-900">
                                                    {formatCurrency(category.budget_limit)}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-right text-gray-900">
                                                    {formatCurrency(category.spent)}
                                                </td>
                                                <td className={`whitespace-nowrap px-6 py-4 text-right ${category.remaining < 0 ? 'font-semibold text-red-600' : 'text-gray-900'}`}>
                                                    {formatCurrency(category.remaining)}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        <Link
                                                            href={route('categories.edit', category.id)}
                                                            className="text-sm text-indigo-600 hover:text-indigo-900"
                                                        >
                                                            Editar
                                                        </Link>
                                                        <DangerButton
                                                            disabled={processing}
                                                            onClick={() => handleDelete(category.id)}
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

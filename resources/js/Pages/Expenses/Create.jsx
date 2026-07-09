import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm } from '@inertiajs/react';

const statusLabels = {
    planned: 'Planeado',
    contracted: 'Contratado',
    paid: 'Pagado',
};

export default function Create({ categories, statuses }) {
    const { data, setData, post, errors, processing } = useForm({
        category_id: '',
        amount: '',
        vendor: '',
        status: 'planned',
        paid_date: '',
        notes: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('expenses.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Crear Gasto
                </h2>
            }
        >
            <Head title="Crear Gasto" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                        <form onSubmit={submit} className="max-w-xl space-y-6">
                            <div>
                                <InputLabel htmlFor="category_id" value="Categoría" />
                                <select
                                    id="category_id"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    value={data.category_id}
                                    onChange={(e) => setData('category_id', e.target.value)}
                                    required
                                >
                                    <option value="">Selecciona una categoría</option>
                                    {categories.map((cat) => (
                                        <option key={cat.id} value={cat.id}>
                                            {cat.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError className="mt-2" message={errors.category_id} />
                            </div>

                            <div>
                                <InputLabel htmlFor="amount" value="Monto" />
                                <TextInput
                                    id="amount"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    className="mt-1 block w-full"
                                    value={data.amount}
                                    onChange={(e) => setData('amount', e.target.value)}
                                    required
                                    isFocused
                                    autoComplete="off"
                                    placeholder="S/. 0.00"
                                />
                                <InputError className="mt-2" message={errors.amount} />
                            </div>

                            <div>
                                <InputLabel htmlFor="vendor" value="Proveedor" />
                                <TextInput
                                    id="vendor"
                                    className="mt-1 block w-full"
                                    value={data.vendor}
                                    onChange={(e) => setData('vendor', e.target.value)}
                                    autoComplete="off"
                                />
                                <InputError className="mt-2" message={errors.vendor} />
                            </div>

                            <div>
                                <InputLabel htmlFor="status" value="Estado" />
                                <select
                                    id="status"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    value={data.status}
                                    onChange={(e) => setData('status', e.target.value)}
                                    required
                                >
                                    {statuses.map((s) => (
                                        <option key={s.value} value={s.value}>
                                            {statusLabels[s.value] || s.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError className="mt-2" message={errors.status} />
                            </div>

                            <div>
                                <InputLabel htmlFor="paid_date" value="Fecha de Pago" />
                                <TextInput
                                    id="paid_date"
                                    type="date"
                                    className="mt-1 block w-full"
                                    value={data.paid_date}
                                    onChange={(e) => setData('paid_date', e.target.value)}
                                />
                                <InputError className="mt-2" message={errors.paid_date} />
                            </div>

                            <div>
                                <InputLabel htmlFor="notes" value="Notas" />
                                <textarea
                                    id="notes"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    rows={3}
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                />
                                <InputError className="mt-2" message={errors.notes} />
                            </div>

                            <div className="flex items-center gap-4">
                                <PrimaryButton disabled={processing}>
                                    Crear Gasto
                                </PrimaryButton>
                                <Link
                                    href={route('expenses.index')}
                                    className="text-sm text-gray-600 hover:text-gray-900"
                                >
                                    Cancelar
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Edit({ wedding, category }) {
    const { data, setData, put, errors, processing } = useForm({
        name: category.name,
        budget_limit: category.budget_limit,
        color: category.color,
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('weddings.categories.update', [wedding.id, category.id]));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Editar Categoría
                </h2>
            }
        >
            <Head title="Editar Categoría" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="clay-card p-6 sm:p-8">
                        <form onSubmit={submit} className="max-w-xl space-y-6">
                            <div>
                                <InputLabel htmlFor="name" value="Nombre" />
                                <TextInput
                                    id="name"
                                    className="mt-1 block w-full"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    required
                                    isFocused
                                    autoComplete="off"
                                />
                                <InputError className="mt-2" message={errors.name} />
                            </div>

                            <div>
                                <InputLabel htmlFor="budget_limit" value="Límite de Presupuesto" />
                                <TextInput
                                    id="budget_limit"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    className="mt-1 block w-full"
                                    value={data.budget_limit}
                                    onChange={(e) => setData('budget_limit', e.target.value)}
                                    required
                                    autoComplete="off"
                                    placeholder="S/. 0.00"
                                />
                                <InputError className="mt-2" message={errors.budget_limit} />
                            </div>

                            <div>
                                <InputLabel htmlFor="color" value="Color" />
                                <div className="mt-1 flex items-center gap-3">
                                    <input
                                        id="color"
                                        type="color"
                                        className="h-10 w-14 cursor-pointer rounded-xl border-none"
                                        value={data.color}
                                        onChange={(e) => setData('color', e.target.value)}
                                    />
                                    <TextInput
                                        className="block w-32"
                                        value={data.color}
                                        onChange={(e) => setData('color', e.target.value)}
                                        maxLength={7}
                                    />
                                </div>
                                <InputError className="mt-2" message={errors.color} />
                            </div>

                            <div className="flex items-center gap-4">
                                <PrimaryButton disabled={processing}>
                                    Actualizar Categoría
                                </PrimaryButton>
                                <Link
                                    href={route('weddings.categories.index', wedding.id)}
                                    className="text-sm font-medium text-gray-500 hover:text-gray-800"
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

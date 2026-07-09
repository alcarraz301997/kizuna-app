import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Create() {
    const { data, setData, post, errors, processing } = useForm({
        name: '',
        budget_limit: '',
        color: '#6366f1',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('categories.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Crear Categoría
                </h2>
            }
        >
            <Head title="Crear Categoría" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-white p-4 shadow sm:rounded-lg sm:p-8">
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
                                    placeholder="Ej: Venue, Fotografía, etc."
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
                                        className="h-10 w-14 cursor-pointer rounded border border-gray-300"
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
                                    Crear Categoría
                                </PrimaryButton>
                                <Link
                                    href={route('categories.index')}
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

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Edit({ table }) {
    const { data, setData, put, errors, processing } = useForm({
        name: table.name,
        capacity: table.capacity,
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('tables.update', table.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Editar Mesa
                </h2>
            }
        >
            <Head title="Editar Mesa" />

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
                                />
                                <InputError className="mt-2" message={errors.name} />
                            </div>

                            <div>
                                <InputLabel htmlFor="capacity" value="Capacidad" />
                                <TextInput
                                    id="capacity"
                                    type="number"
                                    className="mt-1 block w-full"
                                    value={data.capacity}
                                    onChange={(e) => setData('capacity', e.target.value)}
                                    required
                                    autoComplete="off"
                                    min="1"
                                />
                                <InputError className="mt-2" message={errors.capacity} />
                            </div>

                            <div className="flex items-center gap-4">
                                <PrimaryButton disabled={processing}>
                                    Actualizar Mesa
                                </PrimaryButton>
                                <Link
                                    href={route('tables.index')}
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

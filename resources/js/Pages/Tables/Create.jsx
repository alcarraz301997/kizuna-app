import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Create() {
    const { data, setData, post, errors, processing } = useForm({
        name: '',
        capacity: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('tables.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Crear Mesa
                </h2>
            }
        >
            <Head title="Crear Mesa" />

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
                                    placeholder="Ej: Principal"
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
                                    placeholder="Ej: 10"
                                    min="1"
                                />
                                <InputError className="mt-2" message={errors.capacity} />
                            </div>

                            <div className="flex items-center gap-4">
                                <PrimaryButton disabled={processing}>
                                    Crear Mesa
                                </PrimaryButton>
                                <Link
                                    href={route('tables.index')}
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

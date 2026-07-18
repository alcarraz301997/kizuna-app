import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Edit({ guest, rsvpStatuses, tables }) {
    const { data, setData, put, errors, processing } = useForm({
        name: guest.name,
        email: guest.email || '',
        phone: guest.phone || '',
        rsvp_status: guest.rsvp_status,
        table_id: guest.table_id || '',
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('guests.update', guest.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Editar Invitado
                </h2>
            }
        >
            <Head title="Editar Invitado" />

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

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel htmlFor="email" value="Email" />
                                    <TextInput
                                        id="email"
                                        type="email"
                                        className="mt-1 block w-full"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        autoComplete="off"
                                    />
                                    <InputError
                                        className="mt-2"
                                        message={errors.email}
                                    />
                                </div>

                                <div>
                                    <InputLabel htmlFor="phone" value="Teléfono" />
                                    <TextInput
                                        id="phone"
                                        className="mt-1 block w-full"
                                        value={data.phone}
                                        onChange={(e) => setData('phone', e.target.value)}
                                        autoComplete="off"
                                    />
                                    <InputError
                                        className="mt-2"
                                        message={errors.phone}
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel
                                        htmlFor="rsvp_status"
                                        value="Estado RSVP"
                                    />
                                    <select
                                        id="rsvp_status"
                                        className="clay-select mt-1 block w-full"
                                        value={data.rsvp_status}
                                        onChange={(e) =>
                                            setData('rsvp_status', e.target.value)
                                        }
                                    >
                                        {rsvpStatuses.map((status) => (
                                            <option key={status.value} value={status.value}>
                                                {status.label}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError
                                        className="mt-2"
                                        message={errors.rsvp_status}
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        htmlFor="table_id"
                                        value="Mesa"
                                    />
                                    <select
                                        id="table_id"
                                        className="clay-select mt-1 block w-full"
                                        value={data.table_id}
                                        onChange={(e) =>
                                            setData('table_id', e.target.value)
                                        }
                                    >
                                        <option value="">Sin mesa</option>
                                        {tables.map((table) => (
                                            <option
                                                key={table.id}
                                                value={table.id}
                                                disabled={
                                                    table.is_full &&
                                                    String(table.id) !== String(data.table_id)
                                                }
                                            >
                                                {table.name} ({table.guests_count}/{table.capacity})
                                                {table.is_full &&
                                                String(table.id) !== String(data.table_id)
                                                    ? ' — llena'
                                                    : ''}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError
                                        className="mt-2"
                                        message={errors.table_id}
                                    />
                                </div>
                            </div>

                            <div className="flex items-center gap-4">
                                <PrimaryButton disabled={processing}>
                                    Actualizar Invitado
                                </PrimaryButton>
                                <Link
                                    href={route('guests.index')}
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

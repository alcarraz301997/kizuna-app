import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Edit({ vendor, paymentStatuses }) {
    const { data, setData, put, errors, processing } = useForm({
        name: vendor.name,
        service_category: vendor.service_category,
        contact_phone: vendor.contact_phone || '',
        contact_email: vendor.contact_email || '',
        payment_status: vendor.payment_status,
        notes: vendor.notes || '',
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('vendors.update', vendor.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Editar Proveedor
                </h2>
            }
        >
            <Head title="Editar Proveedor" />

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
                                <InputLabel
                                    htmlFor="service_category"
                                    value="Categoría de servicio"
                                />
                                <TextInput
                                    id="service_category"
                                    className="mt-1 block w-full"
                                    value={data.service_category}
                                    onChange={(e) =>
                                        setData('service_category', e.target.value)
                                    }
                                    required
                                    autoComplete="off"
                                />
                                <InputError
                                    className="mt-2"
                                    message={errors.service_category}
                                />
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel
                                        htmlFor="contact_phone"
                                        value="Teléfono"
                                    />
                                    <TextInput
                                        id="contact_phone"
                                        className="mt-1 block w-full"
                                        value={data.contact_phone}
                                        onChange={(e) =>
                                            setData('contact_phone', e.target.value)
                                        }
                                        autoComplete="off"
                                    />
                                    <InputError
                                        className="mt-2"
                                        message={errors.contact_phone}
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        htmlFor="contact_email"
                                        value="Correo electrónico"
                                    />
                                    <TextInput
                                        id="contact_email"
                                        type="email"
                                        className="mt-1 block w-full"
                                        value={data.contact_email}
                                        onChange={(e) =>
                                            setData('contact_email', e.target.value)
                                        }
                                        autoComplete="off"
                                    />
                                    <InputError
                                        className="mt-2"
                                        message={errors.contact_email}
                                    />
                                </div>
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="payment_status"
                                    value="Estado de pago"
                                />
                                <select
                                    id="payment_status"
                                    className="clay-select mt-1 block w-full"
                                    value={data.payment_status}
                                    onChange={(e) =>
                                        setData('payment_status', e.target.value)
                                    }
                                >
                                    {paymentStatuses.map((status) => (
                                        <option key={status.value} value={status.value}>
                                            {status.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError
                                    className="mt-2"
                                    message={errors.payment_status}
                                />
                            </div>

                            <div>
                                <InputLabel htmlFor="notes" value="Notas" />
                                <textarea
                                    id="notes"
                                    className="clay-textarea mt-1 block w-full px-4 py-2.5"
                                    rows={3}
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    placeholder="Detalles adicionales del proveedor..."
                                />
                                <InputError className="mt-2" message={errors.notes} />
                            </div>

                            <div className="flex items-center gap-4">
                                <PrimaryButton disabled={processing}>
                                    Actualizar Proveedor
                                </PrimaryButton>
                                <Link
                                    href={route('vendors.index')}
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

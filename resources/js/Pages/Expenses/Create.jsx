import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';

const statusLabels = {
    planned: 'Planeado',
    contracted: 'Contratado',
    paid: 'Pagado',
};

export default function Create({ categories, vendors, statuses }) {
    const { data, setData, post, errors, processing } = useForm({
        category_id: '',
        amount: '',
        vendor_text: '',
        vendor_id: '',
        status: 'planned',
        paid_date: '',
        notes: '',
        receipt_files: [],
    });

    const [vendorMode, setVendorMode] = useState('select'); // 'select' | 'text'
    const [receiptErrors, setReceiptErrors] = useState([]);

    const handleVendorSelect = (e) => {
        const mode = e.target.value;
        setVendorMode(mode);
        if (mode === 'text') {
            setData('vendor_id', '');
        } else {
            setData('vendor_text', '');
        }
    };

    const handleVendorDropdown = (e) => {
        setData('vendor_id', e.target.value);
    };

    const handleFileSelect = (e) => {
        const files = Array.from(e.target.files || []);
        const errors = [];
        const validFiles = [];

        if (files.length > 5) {
            errors.push('Máximo 5 archivos por gasto');
            e.target.value = '';
            setReceiptErrors(errors);
            return;
        }

        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
        const maxSize = 10 * 1024 * 1024; // 10 MB

        for (const file of files) {
            if (file.size > maxSize) {
                errors.push(`"${file.name}" excede 10 MB`);
                continue;
            }
            if (!allowedTypes.includes(file.type)) {
                errors.push(`"${file.name}" no es un tipo permitido (JPEG, PNG, GIF, WebP, PDF)`);
                continue;
            }
            validFiles.push(file);
        }

        setReceiptErrors(errors);
        setData('receipt_files', validFiles);
    };

    const submit = (e) => {
        e.preventDefault();

        // Combine vendor data: if select mode, pass vendor_id; if text mode, pass vendor_text as vendor
        const vendorData = vendorMode === 'select'
            ? { vendor_id: data.vendor_id || null, vendor: '' }
            : { vendor_id: null, vendor: data.vendor_text };

        const formData = { ...data, ...vendorData };

        post(route('expenses.store'), {
            data: formData,
            forceFormData: true,
            onSuccess: () => {
                // Reset receipt files on success
                setData('receipt_files', []);
            },
        });
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
                                <InputLabel htmlFor="vendor_mode" value="Proveedor" />
                                <select
                                    id="vendor_mode"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    value={vendorMode}
                                    onChange={handleVendorSelect}
                                >
                                    <option value="select">Seleccionar proveedor</option>
                                    <option value="text">Otro (texto libre)</option>
                                </select>
                            </div>

                            {vendorMode === 'select' ? (
                                <div>
                                    <InputLabel htmlFor="vendor_id" value="Proveedor registrado" />
                                    <select
                                        id="vendor_id"
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        value={data.vendor_id}
                                        onChange={handleVendorDropdown}
                                    >
                                        <option value="">Ninguno</option>
                                        {vendors.map((v) => (
                                            <option key={v.id} value={v.id}>
                                                {v.name}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError className="mt-2" message={errors.vendor_id} />
                                </div>
                            ) : (
                                <div>
                                    <InputLabel htmlFor="vendor_text" value="Nombre del proveedor" />
                                    <TextInput
                                        id="vendor_text"
                                        className="mt-1 block w-full"
                                        value={data.vendor_text}
                                        onChange={(e) => setData('vendor_text', e.target.value)}
                                        autoComplete="off"
                                        placeholder="Ej: Florería Local"
                                    />
                                    <InputError className="mt-2" message={errors.vendor} />
                                </div>
                            )}

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

                            <div>
                                <InputLabel htmlFor="receipt_files" value="Adjuntos (Recibos)" />
                                <input
                                    id="receipt_files"
                                    type="file"
                                    multiple
                                    accept="image/jpeg,image/png,image/gif,image/webp,application/pdf"
                                    className="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                                    onChange={handleFileSelect}
                                />
                                <p className="mt-1 text-xs text-gray-500">
                                    Máximo 5 archivos. Imágenes (JPEG, PNG, GIF, WebP) o PDF. Hasta 10 MB cada uno.
                                </p>
                                {receiptErrors.length > 0 && (
                                    <ul className="mt-2 space-y-1">
                                        {receiptErrors.map((err, i) => (
                                            <li key={i} className="text-sm text-red-600">{err}</li>
                                        ))}
                                    </ul>
                                )}
                                {data.receipt_files.length > 0 && (
                                    <ul className="mt-2 space-y-1">
                                        {data.receipt_files.map((file, i) => (
                                            <li key={i} className="text-sm text-gray-600">
                                                ✓ {file.name} ({(file.size / 1024).toFixed(0)} KB)
                                            </li>
                                        ))}
                                    </ul>
                                )}
                                <InputError className="mt-2" message={errors.receipt_files} />
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

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';

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
        split_type: '',
        person_a_label: 'Él',
        person_b_label: 'Ella',
        person_a_amount: '',
        person_b_amount: '',
        percent_a: '50',
    });

    const [vendorMode, setVendorMode] = useState('select'); // 'select' | 'text'
    const [receiptErrors, setReceiptErrors] = useState([]);

    // Auto-calculate split amounts for 50_50 and percent
    const numAmount = parseFloat(data.amount) || 0;
    useEffect(() => {
        if (data.split_type === '50_50' && numAmount > 0) {
            const a = Math.round((numAmount / 2) * 100) / 100;
            const b = Math.round((numAmount - a) * 100) / 100;
            setData('person_a_amount', a.toString());
            setData('person_b_amount', b.toString());
        } else if (data.split_type === 'percent' && numAmount > 0) {
            const pct = parseFloat(data.percent_a) || 0;
            const a = Math.round((numAmount * (pct / 100)) * 100) / 100;
            const b = Math.round((numAmount - a) * 100) / 100;
            setData('person_a_amount', a.toString());
            setData('person_b_amount', Math.max(0, b).toString());
        }
    }, [data.split_type, data.amount, data.percent_a]);

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

                            {/* --- Splitting de gastos --- */}
                            <div className="border-t pt-6">
                                <h3 className="mb-4 text-lg font-medium text-gray-900">
                                    División del gasto
                                </h3>

                                <div className="space-y-4">
                                    <div>
                                        <InputLabel htmlFor="split_type_create" value="Tipo de división" />
                                        <select
                                            id="split_type_create"
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            value={data.split_type}
                                            onChange={(e) => {
                                                const newType = e.target.value;
                                                setData('split_type', newType);
                                                if (newType === 'fixed') {
                                                    setData('person_a_amount', '');
                                                    setData('person_b_amount', '');
                                                } else if (newType === 'percent') {
                                                    setData('percent_a', '50');
                                                }
                                            }}
                                        >
                                            <option value="">Sin dividir</option>
                                            <option value="50_50">50 / 50</option>
                                            <option value="percent">Porcentaje</option>
                                            <option value="fixed">Monto fijo</option>
                                        </select>
                                        <InputError className="mt-2" message={errors.split_type} />
                                    </div>

                                    {data.split_type && (
                                        <>
                                            <div className="grid grid-cols-2 gap-4">
                                                <div>
                                                    <InputLabel htmlFor="person_a_label_create" value="Etiqueta persona A" />
                                                    <TextInput
                                                        id="person_a_label_create"
                                                        className="mt-1 block w-full"
                                                        value={data.person_a_label}
                                                        onChange={(e) => setData('person_a_label', e.target.value)}
                                                        autoComplete="off"
                                                    />
                                                    <InputError className="mt-2" message={errors.person_a_label} />
                                                </div>
                                                <div>
                                                    <InputLabel htmlFor="person_b_label_create" value="Etiqueta persona B" />
                                                    <TextInput
                                                        id="person_b_label_create"
                                                        className="mt-1 block w-full"
                                                        value={data.person_b_label}
                                                        onChange={(e) => setData('person_b_label', e.target.value)}
                                                        autoComplete="off"
                                                    />
                                                    <InputError className="mt-2" message={errors.person_b_label} />
                                                </div>
                                            </div>

                                            {data.split_type === 'percent' && (
                                                <div>
                                                    <InputLabel htmlFor="percent_a_create" value={`Porcentaje para ${data.person_a_label || 'Persona A'}`} />
                                                    <div className="mt-1 flex items-center gap-2">
                                                        <TextInput
                                                            id="percent_a_create"
                                                            type="number"
                                                            min="0"
                                                            max="100"
                                                            className="block w-24"
                                                            value={data.percent_a}
                                                            onChange={(e) => setData('percent_a', e.target.value)}
                                                            autoComplete="off"
                                                        />
                                                        <span className="text-sm text-gray-500">%</span>
                                                        <span className="text-sm text-gray-400">
                                                            ({data.person_b_label || 'Persona B'}: {100 - (parseFloat(data.percent_a) || 0)}%)
                                                        </span>
                                                    </div>
                                                    <InputError className="mt-2" message={errors.percent_a} />
                                                </div>
                                            )}

                                            {data.split_type === 'fixed' && (
                                                <div className="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <InputLabel htmlFor="person_a_amount_create" value={`Monto ${data.person_a_label || 'Persona A'}`} />
                                                        <TextInput
                                                            id="person_a_amount_create"
                                                            type="number"
                                                            step="0.01"
                                                            min="0"
                                                            className="mt-1 block w-full"
                                                            value={data.person_a_amount}
                                                            onChange={(e) => setData('person_a_amount', e.target.value)}
                                                            autoComplete="off"
                                                            placeholder="S/. 0.00"
                                                        />
                                                        <InputError className="mt-2" message={errors.person_a_amount} />
                                                    </div>
                                                    <div>
                                                        <InputLabel htmlFor="person_b_amount_create" value={`Monto ${data.person_b_label || 'Persona B'}`} />
                                                        <TextInput
                                                            id="person_b_amount_create"
                                                            type="number"
                                                            step="0.01"
                                                            min="0"
                                                            className="mt-1 block w-full"
                                                            value={data.person_b_amount}
                                                            onChange={(e) => setData('person_b_amount', e.target.value)}
                                                            autoComplete="off"
                                                            placeholder="S/. 0.00"
                                                        />
                                                        <InputError className="mt-2" message={errors.person_b_amount} />
                                                    </div>
                                                </div>
                                            )}

                                            {(data.split_type === '50_50' || data.split_type === 'percent') && numAmount > 0 && (
                                                <div className="rounded-md bg-gray-50 p-4">
                                                    <h4 className="mb-2 text-sm font-medium text-gray-700">Montos calculados</h4>
                                                    <div className="grid grid-cols-2 gap-4">
                                                        <div>
                                                            <span className="text-sm text-gray-500">{data.person_a_label || 'Persona A'}</span>
                                                            <p className="text-lg font-semibold text-gray-900">
                                                                S/. {parseFloat(data.person_a_amount || 0).toFixed(2)}
                                                            </p>
                                                        </div>
                                                        <div>
                                                            <span className="text-sm text-gray-500">{data.person_b_label || 'Persona B'}</span>
                                                            <p className="text-lg font-semibold text-gray-900">
                                                                S/. {parseFloat(data.person_b_amount || 0).toFixed(2)}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <p className="mt-2 text-xs text-gray-400">
                                                        Total: S/. {numAmount.toFixed(2)}
                                                    </p>
                                                </div>
                                            )}

                                            {data.split_type === 'fixed' && (
                                                <div className="rounded-md bg-gray-50 p-3">
                                                    <p className="text-sm text-gray-600">
                                                        Suma: S/.{' '}
                                                        {((parseFloat(data.person_a_amount) || 0) + (parseFloat(data.person_b_amount) || 0)).toFixed(2)}
                                                        {' '}/ S/. {numAmount.toFixed(2)}
                                                    </p>
                                                </div>
                                            )}
                                        </>
                                    )}
                                </div>
                            </div>

                            {/* Adjuntos */}
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

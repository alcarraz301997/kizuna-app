import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import ReceiptPreview from '@/Components/ReceiptPreview';
import SplitForm from '@/Components/SplitForm';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';

const statusLabels = {
    planned: 'Planeado',
    contracted: 'Contratado',
    paid: 'Pagado',
};

export default function Edit({ wedding, expense, categories, vendors, receipts, statuses, maxReceipts, commitment: initialCommitment }) {
    const { data, setData, put, errors, processing } = useForm({
        category_id: expense.category_id,
        amount: expense.amount,
        vendor_text: expense.vendor || '',
        vendor_id: expense.vendor_id || '',
        status: expense.status,
        paid_date: expense.paid_date || '',
        notes: expense.notes || '',
        receipt_files: [],
    });

    const [vendorMode, setVendorMode] = useState(expense.vendor_id ? 'select' : (expense.vendor ? 'text' : 'select'));
    const [receiptErrors, setReceiptErrors] = useState([]);
    const [commitment, setCommitment] = useState(initialCommitment || { planned_amount: null, contracted_amount: null, paid_to_date: 0, balance: null, due_date: '' });
    const commitmentForm = useForm({ planned_amount: commitment.planned_amount ?? '', contracted_amount: commitment.contracted_amount ?? '', due_date: commitment.due_date || '' });
    const paymentForm = useForm({ amount: '', paid_on: '', kind: 'payment' });

    const updateCommitment = (event) => {
        event.preventDefault();
        commitmentForm.patch(`/weddings/${expense.wedding_id}/expenses/${expense.id}/commitment`, {
            onSuccess: (page) => page?.props?.commitment && setCommitment(page.props.commitment),
        });
    };
    const addPayment = (event) => {
        event.preventDefault();
        paymentForm.post(`/weddings/${expense.wedding_id}/expenses/${expense.id}/payments`, {
            onSuccess: (page) => {
                if (page?.props?.commitment) setCommitment(page.props.commitment);
                paymentForm.reset();
            },
        });
    };

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

        const currentCount = receipts.length;
        if (currentCount + files.length > maxReceipts) {
            errors.push(`Máximo ${maxReceipts} archivos por gasto (ya tienes ${currentCount})`);
            e.target.value = '';
            setReceiptErrors(errors);
            return;
        }

        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
        const maxSize = 10 * 1024 * 1024;

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

        const vendorData = vendorMode === 'select'
            ? { vendor_id: data.vendor_id || null, vendor: '' }
            : { vendor_id: null, vendor: data.vendor_text };

        const formData = { ...data, ...vendorData };

        put(route('weddings.expenses.update', [wedding.id, expense.id]), {
            data: formData,
            forceFormData: true,
            onSuccess: () => {
                setData('receipt_files', []);
            },
        });
    };

    const handleUploadReceipts = () => {
        if (data.receipt_files.length === 0) return;

        const uploadNext = (index) => {
            if (index >= data.receipt_files.length) {
                setData('receipt_files', []);
                window.location.reload();
                return;
            }

            const formData = new FormData();
            formData.append('receipt', data.receipt_files[index]);

            fetch(route('weddings.expenses.receipts.store', [wedding.id, expense.id]), {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            }).then(() => {
                uploadNext(index + 1);
            });
        };

        uploadNext(0);
    };

    const receiptCount = receipts.length;
    const canUploadMore = receiptCount < maxReceipts;

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Editar Gasto
                </h2>
            }
        >
            <Head title="Editar Gasto" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="space-y-6">
                        <div className="clay-card p-6 sm:p-8">
                            <form onSubmit={submit} className="max-w-xl space-y-6">
                                <div>
                                    <InputLabel htmlFor="category_id" value="Categoría" />
                                    <select
                                        id="category_id"
                                        className="clay-select mt-1 block w-full"
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
                                        className="clay-select mt-1 block w-full"
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
                                            className="clay-select mt-1 block w-full"
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
                                        className="clay-select mt-1 block w-full"
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
                                        className="clay-textarea mt-1 block w-full px-4 py-2.5"
                                        rows={3}
                                        value={data.notes}
                                        onChange={(e) => setData('notes', e.target.value)}
                                    />
                                    <InputError className="mt-2" message={errors.notes} />
                                </div>

                                <div className="flex items-center gap-4">
                                    <PrimaryButton disabled={processing}>
                                        Actualizar Gasto
                                    </PrimaryButton>
                                    <Link
                                        href={route('weddings.expenses.index', wedding?.id || expense.wedding_id)}
                                        className="text-sm font-medium text-gray-500 hover:text-gray-800"
                                    >
                                        Cancelar
                                    </Link>
                                </div>
                            </form>
                        </div>

                        <div className="clay-card p-6 sm:p-8">
                            <h3 className="mb-4 text-lg font-semibold text-gray-900">Compromiso y pagos</h3>
                            <div className="mb-6 grid gap-3 sm:grid-cols-2"><div><span className="text-sm text-gray-500">Pagado hasta hoy</span><p className="text-xl font-bold">S/. {Number(commitment.paid_to_date || 0).toFixed(2)}</p></div><div><span className="text-sm text-gray-500">Saldo</span><p className="text-xl font-bold">{commitment.balance === null ? 'Sin monto contratado' : `S/. ${Number(commitment.balance).toFixed(2)}`}</p></div></div>
                            <form onSubmit={updateCommitment} className="grid gap-4 border-b border-gray-200/60 pb-6 sm:grid-cols-3">
                                <div><InputLabel htmlFor="planned_amount" value="Monto planeado" /><TextInput id="planned_amount" type="number" min="0" step="0.01" className="mt-1 block w-full" value={commitmentForm.data.planned_amount} onChange={(event) => commitmentForm.setData('planned_amount', event.target.value)} /><InputError className="mt-1" message={commitmentForm.errors.planned_amount} /></div>
                                <div><InputLabel htmlFor="contracted_amount" value="Monto contratado" /><TextInput id="contracted_amount" type="number" min="0" step="0.01" className="mt-1 block w-full" value={commitmentForm.data.contracted_amount} onChange={(event) => commitmentForm.setData('contracted_amount', event.target.value)} /><InputError className="mt-1" message={commitmentForm.errors.contracted_amount} /></div>
                                <div><InputLabel htmlFor="due_date" value="Fecha de vencimiento" /><TextInput id="due_date" type="date" className="mt-1 block w-full" value={commitmentForm.data.due_date} onChange={(event) => commitmentForm.setData('due_date', event.target.value)} /><InputError className="mt-1" message={commitmentForm.errors.due_date} /></div>
                                <PrimaryButton disabled={commitmentForm.processing}>Guardar compromiso</PrimaryButton>
                            </form>
                            <form onSubmit={addPayment} className="mt-6 grid gap-4 sm:grid-cols-3"><div><InputLabel htmlFor="payment_amount" value="Nuevo pago" /><TextInput id="payment_amount" type="number" min="0" step="0.01" className="mt-1 block w-full" value={paymentForm.data.amount} onChange={(event) => paymentForm.setData('amount', event.target.value)} required /><InputError className="mt-1" message={paymentForm.errors.amount} /></div><div><InputLabel htmlFor="payment_date" value="Fecha del pago" /><TextInput id="payment_date" type="date" className="mt-1 block w-full" value={paymentForm.data.paid_on} onChange={(event) => paymentForm.setData('paid_on', event.target.value)} /><InputError className="mt-1" message={paymentForm.errors.paid_on} /></div><div className="self-end"><PrimaryButton disabled={paymentForm.processing}>Registrar pago</PrimaryButton></div></form>
                        </div>

                        <div className="clay-card p-6 sm:p-8">
                            <h3 className="mb-4 text-lg font-semibold text-gray-900">
                                Adjuntos ({receiptCount}/{maxReceipts})
                            </h3>

                            <ReceiptPreview weddingId={wedding?.id || expense.wedding_id} receipts={receipts} />

                            {canUploadMore && (
                                <div className="mt-6 border-t border-gray-200/50 pt-4">
                                    <InputLabel htmlFor="receipt_files" value="Agregar adjuntos" />
                                    <input
                                        id="receipt_files"
                                        type="file"
                                        multiple
                                        accept="image/jpeg,image/png,image/gif,image/webp,application/pdf"
                                        className="clay-file-input mt-1 block w-full text-sm text-gray-500"
                                        onChange={handleFileSelect}
                                    />
                                    <p className="mt-1 text-xs text-gray-500">
                                        Máximo {maxReceipts} archivos en total. Imágenes o PDF. Hasta 10 MB cada uno.
                                    </p>
                                    {receiptErrors.length > 0 && (
                                        <ul className="mt-2 space-y-1">
                                            {receiptErrors.map((err, i) => (
                                                <li key={i} className="text-sm text-red-600">{err}</li>
                                            ))}
                                        </ul>
                                    )}
                                    {data.receipt_files.length > 0 && (
                                        <div className="mt-3">
                                            <ul className="space-y-1">
                                                {data.receipt_files.map((file, i) => (
                                                    <li key={i} className="text-sm text-gray-600">
                                                        ✓ {file.name} ({(file.size / 1024).toFixed(0)} KB)
                                                    </li>
                                                ))}
                                            </ul>
                                            <button
                                                type="button"
                                                onClick={handleUploadReceipts}
                                                className="clay-btn clay-btn-primary mt-3 inline-flex items-center px-5 py-2.5 text-xs font-semibold uppercase tracking-widest"
                                            >
                                                Subir {data.receipt_files.length} archivo{data.receipt_files.length !== 1 ? 's' : ''}
                                            </button>
                                        </div>
                                    )}
                                    <InputError className="mt-2" message={errors.receipt_files} />
                                </div>
                            )}

                            {!canUploadMore && (
                                <div className="mt-6 border-t border-gray-200/50 pt-4">
                                    <p className="text-sm text-gray-500">
                                        Has alcanzado el límite de {maxReceipts} adjuntos. Elimina alguno para agregar más.
                                    </p>
                                </div>
                            )}
                        </div>

                        <div className="clay-card p-6 sm:p-8">
                            <h3 className="mb-4 text-lg font-semibold text-gray-900">
                                División del gasto
                            </h3>

                            {expense.split ? (
                                <>
                                    <div className="clay-card clay-card-sky mb-6 p-4">
                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <span className="text-sm text-gray-500">{expense.split.person_a_label}</span>
                                                <p className="text-lg font-bold text-gray-900">
                                                    S/. {expense.split.person_a_amount.toFixed(2)}
                                                </p>
                                            </div>
                                            <div>
                                                <span className="text-sm text-gray-500">{expense.split.person_b_label}</span>
                                                <p className="text-lg font-bold text-gray-900">
                                                    S/. {expense.split.person_b_amount.toFixed(2)}
                                                </p>
                                            </div>
                                        </div>
                                        <p className="mt-2 text-xs text-gray-400">
                                            Tipo: {expense.split.split_type === '50_50' ? '50 / 50' : expense.split.split_type === 'percent' ? 'Porcentaje' : 'Monto fijo'}
                                            {' '}· Total: S/. {expense.amount.toFixed(2)}
                                        </p>
                                    </div>

                                    <details className="group">
                                        <summary className="cursor-pointer text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                                            Editar división
                                        </summary>
                                        <div className="mt-4">
                                            <SplitForm
                                                weddingId={wedding.id}
                                                expenseId={expense.id}
                                                amount={expense.amount}
                                                split={expense.split}
                                                standalone={true}
                                                onSaved={() => window.location.reload()}
                                            />
                                        </div>
                                    </details>
                                </>
                            ) : (
                                <SplitForm
                                    weddingId={wedding?.id || expense.wedding_id}
                                    expenseId={expense.id}
                                    amount={expense.amount}
                                    split={null}
                                    standalone={true}
                                    onSaved={() => window.location.reload()}
                                />
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

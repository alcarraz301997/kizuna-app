import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';

const SPLIT_LABELS = {
    '50_50': '50 / 50',
    percent: 'Porcentaje',
    fixed: 'Monto fijo',
};

/**
 * SplitForm — reusable component for creating/editing expense splits.
 *
 * Props:
 *   expenseId   — expense ID (required for standalone mode)
 *   amount      — expense amount (for auto-calculations)
 *   split       — existing split data or null
 *   standalone  — if true, wraps in its own form with submit button
 *   onSaved     — callback after successful save (standalone mode)
 */
export default function SplitForm({ expenseId, amount = 0, split = null, standalone = true, onSaved }) {
    const [splitType, setSplitType] = useState(split?.split_type || '');
    const [personALabel, setPersonALabel] = useState(split?.person_a_label || 'Él');
    const [personBLabel, setPersonBLabel] = useState(split?.person_b_label || 'Ella');
    const [personAAmount, setPersonAAmount] = useState(split?.person_a_amount?.toString() || '');
    const [personBAmount, setPersonBAmount] = useState(split?.person_b_amount?.toString() || '');
    const [percentA, setPercentA] = useState('50');

    const { data, setData, post, put, errors, processing } = useForm({
        split_type: split?.split_type || '',
        person_a_label: split?.person_a_label || 'Él',
        person_b_label: split?.person_b_label || 'Ella',
        person_a_amount: split?.person_a_amount?.toString() || '',
        person_b_amount: split?.person_b_amount?.toString() || '',
        percent_a: '50',
    });

    const numAmount = parseFloat(amount) || 0;

    // Calculate amounts for 50_50 and percent types
    useEffect(() => {
        if (splitType === '50_50' && numAmount > 0) {
            const a = Math.round((numAmount / 2) * 100) / 100;
            const b = Math.round((numAmount - a) * 100) / 100;
            setPersonAAmount(a.toString());
            setPersonBAmount(b.toString());
        } else if (splitType === 'percent' && numAmount > 0) {
            const pct = parseFloat(percentA) || 0;
            const a = Math.round((numAmount * (pct / 100)) * 100) / 100;
            const b = Math.round((numAmount - a) * 100) / 100;
            setPersonAAmount(a.toString());
            setPersonBAmount(Math.max(0, b).toString());
        }
    }, [splitType, numAmount, percentA]);

    // Sync state to form data
    useEffect(() => {
        setData({
            split_type: splitType,
            person_a_label: personALabel,
            person_b_label: personBLabel,
            person_a_amount: personAAmount,
            person_b_amount: personBAmount,
            percent_a: percentA,
        });
    }, [splitType, personALabel, personBLabel, personAAmount, personBAmount, percentA]);

    const handleSplitTypeChange = (e) => {
        const newType = e.target.value;
        setSplitType(newType);

        if (newType === 'fixed') {
            setPersonAAmount('');
            setPersonBAmount('');
        } else if (newType === 'percent') {
            setPercentA('50');
        } else {
            setPercentA('50');
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        const url = split
            ? route('expenses.split.update', expenseId)
            : route('expenses.split.store', expenseId);

        const method = split ? put : post;

        method(url, {
            onSuccess: () => {
                if (onSaved) onSaved();
            },
        });
    };

    const showAmounts = splitType === '50_50' || splitType === 'percent';
    const showFixed = splitType === 'fixed';
    const showPercent = splitType === 'percent';

    const formContent = (
        <div className="space-y-4">
            <div>
                <InputLabel htmlFor="split_type" value="Tipo de división" />
                <select
                    id="split_type"
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    value={splitType}
                    onChange={handleSplitTypeChange}
                >
                    <option value="">Sin dividir</option>
                    <option value="50_50">50 / 50</option>
                    <option value="percent">Porcentaje</option>
                    <option value="fixed">Monto fijo</option>
                </select>
                <InputError className="mt-2" message={errors.split_type} />
            </div>

            {splitType && (
                <>
                    {/* Labels */}
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel htmlFor="person_a_label" value="Etiqueta persona A" />
                            <TextInput
                                id="person_a_label"
                                className="mt-1 block w-full"
                                value={personALabel}
                                onChange={(e) => setPersonALabel(e.target.value)}
                                autoComplete="off"
                            />
                            <InputError className="mt-2" message={errors.person_a_label} />
                        </div>
                        <div>
                            <InputLabel htmlFor="person_b_label" value="Etiqueta persona B" />
                            <TextInput
                                id="person_b_label"
                                className="mt-1 block w-full"
                                value={personBLabel}
                                onChange={(e) => setPersonBLabel(e.target.value)}
                                autoComplete="off"
                            />
                            <InputError className="mt-2" message={errors.person_b_label} />
                        </div>
                    </div>

                    {/* Percent input */}
                    {showPercent && (
                        <div>
                            <InputLabel htmlFor="percent_a" value={`Porcentaje para ${personALabel || 'Persona A'}`} />
                            <div className="mt-1 flex items-center gap-2">
                                <TextInput
                                    id="percent_a"
                                    type="number"
                                    min="0"
                                    max="100"
                                    className="block w-24"
                                    value={percentA}
                                    onChange={(e) => setPercentA(e.target.value)}
                                    autoComplete="off"
                                />
                                <span className="text-sm text-gray-500">%</span>
                                <span className="text-sm text-gray-400">
                                    ({personBLabel || 'Persona B'}: {100 - (parseFloat(percentA) || 0)}%)
                                </span>
                            </div>
                            <InputError className="mt-2" message={errors.percent_a} />
                        </div>
                    )}

                    {/* Fixed amounts */}
                    {showFixed && (
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel htmlFor="person_a_amount" value={`Monto ${personALabel || 'Persona A'}`} />
                                <TextInput
                                    id="person_a_amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    className="mt-1 block w-full"
                                    value={personAAmount}
                                    onChange={(e) => setPersonAAmount(e.target.value)}
                                    autoComplete="off"
                                    placeholder="S/. 0.00"
                                />
                                <InputError className="mt-2" message={errors.person_a_amount} />
                            </div>
                            <div>
                                <InputLabel htmlFor="person_b_amount" value={`Monto ${personBLabel || 'Persona B'}`} />
                                <TextInput
                                    id="person_b_amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    className="mt-1 block w-full"
                                    value={personBAmount}
                                    onChange={(e) => setPersonBAmount(e.target.value)}
                                    autoComplete="off"
                                    placeholder="S/. 0.00"
                                />
                                <InputError className="mt-2" message={errors.person_b_amount} />
                            </div>
                        </div>
                    )}

                    {/* Calculated amounts (read-only for 50_50 and percent) */}
                    {showAmounts && (
                        <div className="rounded-md bg-gray-50 p-4">
                            <h4 className="mb-2 text-sm font-medium text-gray-700">Montos calculados</h4>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <span className="text-sm text-gray-500">{personALabel || 'Persona A'}</span>
                                    <p className="text-lg font-semibold text-gray-900">
                                        S/. {parseFloat(personAAmount || 0).toFixed(2)}
                                    </p>
                                </div>
                                <div>
                                    <span className="text-sm text-gray-500">{personBLabel || 'Persona B'}</span>
                                    <p className="text-lg font-semibold text-gray-900">
                                        S/. {parseFloat(personBAmount || 0).toFixed(2)}
                                    </p>
                                </div>
                            </div>
                            <p className="mt-2 text-xs text-gray-400">
                                Total: S/. {numAmount.toFixed(2)}
                            </p>
                        </div>
                    )}

                    {/* Fixed sum display */}
                    {showFixed && (
                        <div className="rounded-md bg-gray-50 p-3">
                            <p className="text-sm text-gray-600">
                                Suma: S/.{' '}
                                {((parseFloat(personAAmount) || 0) + (parseFloat(personBAmount) || 0)).toFixed(2)}
                                {' '}/ S/. {numAmount.toFixed(2)}
                            </p>
                        </div>
                    )}
                </>
            )}
        </div>
    );

    if (standalone) {
        return (
            <form onSubmit={handleSubmit} className="space-y-4">
                {formContent}

                {splitType && (
                    <div className="flex items-center gap-4">
                        <PrimaryButton disabled={processing}>
                            {split ? 'Actualizar división' : 'Guardar división'}
                        </PrimaryButton>
                    </div>
                )}
            </form>
        );
    }

    // Embedded mode: just return the content for parent form
    return formContent;
}

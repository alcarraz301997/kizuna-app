import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatCurrency } from '@/utils/formatCurrency';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

export default function Dashboard({ categories, totals }) {
    const { wedding } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({
        name: '',
    });

    const createWedding = (event) => {
        event.preventDefault();
        post('/weddings');
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Panel
                </h2>
            }
        >
            <Head title="Panel" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <section className="clay-card clay-card-indigo mb-8 p-5 sm:p-6" aria-labelledby="wedding-setup-title">
                        {wedding ? (
                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 id="wedding-setup-title" className="text-lg font-semibold text-gray-900">
                                        {wedding.name}
                                    </h3>
                                    <p className="mt-1 text-sm text-gray-600">
                                        Continúa organizando el presupuesto y los detalles de tu boda.
                                    </p>
                                </div>
                                <Link
                                    href={`/weddings/${wedding.id}`}
                                    className="clay-btn clay-btn-primary inline-flex min-h-touch items-center justify-center px-5 py-2.5 text-sm font-semibold"
                                >
                                    Ir al espacio de trabajo
                                </Link>
                            </div>
                        ) : (
                            <div className="max-w-2xl">
                                <h3 id="wedding-setup-title" className="text-lg font-semibold text-gray-900">
                                    Comienza a planificar tu boda
                                </h3>
                                <p className="mt-1 text-sm text-gray-600">
                                    Crea tu espacio de trabajo para acceder a las herramientas de planificación.
                                </p>
                                <form onSubmit={createWedding} className="mt-5 space-y-4">
                                    <div>
                                        <InputLabel htmlFor="wedding_name" value="Nombre de la boda" />
                                        <TextInput
                                            id="wedding_name"
                                            name="name"
                                            value={data.name}
                                            onChange={(event) => setData('name', event.target.value)}
                                            className="mt-1 block w-full"
                                            autoComplete="off"
                                            required
                                            aria-invalid={Boolean(errors.name)}
                                            aria-describedby={errors.name ? 'wedding_name_error' : undefined}
                                        />
                                        <InputError
                                            id="wedding_name_error"
                                            message={errors.name}
                                            className="mt-2"
                                            role="alert"
                                        />
                                    </div>
                                    <PrimaryButton disabled={processing} aria-live="polite">
                                        {processing ? 'Creando espacio...' : 'Crear espacio de boda'}
                                    </PrimaryButton>
                                </form>
                            </div>
                        )}
                    </section>

                    <div className="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                        <div className="clay-card clay-card-indigo p-5 sm:p-6">
                            <p className="text-sm font-medium text-primary">Presupuesto Total</p>
                            <p className="mt-2 text-xl sm:text-2xl font-bold text-gray-900">
                                {formatCurrency(totals.total_budget)}
                            </p>
                        </div>
                        <div className="clay-card clay-card-rose p-5 sm:p-6">
                            <p className="text-sm font-medium text-accent">Total Gastado</p>
                            <p className="mt-2 text-xl sm:text-2xl font-bold text-gray-900">
                                {formatCurrency(totals.total_spent)}
                            </p>
                        </div>
                        <div className="clay-card clay-card-amber p-5 sm:p-6">
                            <p className="text-sm font-medium text-warning">Planeado</p>
                            <p className="mt-2 text-xl sm:text-2xl font-bold text-gray-900">
                                {formatCurrency(totals.total_planned)}
                            </p>
                        </div>
                        <div className="clay-card clay-card-sky p-5 sm:p-6">
                            <p className="text-sm font-medium text-primary">Contratado</p>
                            <p className="mt-2 text-xl sm:text-2xl font-bold text-gray-900">{formatCurrency(totals.total_contracted || 0)}</p>
                        </div>
                        <div className="clay-card clay-card-emerald p-5 sm:p-6">
                            <p className="text-sm font-medium text-success">Pagado</p>
                            <p className="mt-2 text-xl sm:text-2xl font-bold text-gray-900">{formatCurrency(totals.total_paid || 0)}</p>
                        </div>
                        <div className="clay-card clay-card-emerald p-5 sm:p-6">
                            <p className="text-sm font-medium text-success">Restante</p>
                            <p className={`mt-2 text-xl sm:text-2xl font-bold ${totals.total_remaining < 0 ? 'text-red-600' : 'text-gray-900'}`}>
                                {formatCurrency(totals.total_remaining)}
                            </p>
                        </div>
                    </div>

                    <div className="clay-card p-4 sm:p-6">
                        <h3 className="mb-6 text-lg font-semibold text-gray-900">
                            Desglose por Categoría
                        </h3>
                        {categories.length === 0 ? (
                            <p className="text-center text-gray-500">
                                Aún no hay categorías. Crea categorías para ver el progreso del presupuesto.
                            </p>
                        ) : (
                            <div className="space-y-6">
                                {categories.map((category) => (
                                    <div key={category.id}>
                                        <div className="mb-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                            <div className="flex items-center">
                                                <span
                                                    className="mr-2 inline-block h-4 w-4 rounded-full shrink-0"
                                                    style={{ backgroundColor: category.color }}
                                                />
                                                <span className="font-medium text-gray-900 text-sm sm:text-base">
                                                    {category.name}
                                                </span>
                                            </div>
                                            <div className="text-sm text-gray-500">
                                                {formatCurrency(category.spent)} / {formatCurrency(category.budget_limit)}
                                                <span className={`ml-2 font-semibold ${category.progress > 100 ? 'text-red-600' : 'text-gray-700'}`}>
                                                    ({category.progress}%)
                                                </span>
                                            </div>
                                        </div>
                                        <div className="clay-input h-4 w-full overflow-hidden !shadow-none">
                                            <div
                                                className={`h-full rounded-full transition-all ${category.progress > 100 ? 'bg-red-400' : category.progress > 80 ? 'bg-amber-400' : 'bg-indigo-400'}`}
                                                style={{ width: `${Math.min(category.progress, 100)}%` }}
                                            />
                                        </div>
                                        <div className="mt-1 flex justify-between text-xs text-gray-500">
                                            <span>
                                                Restante: <span className={category.remaining < 0 ? 'font-semibold text-red-600' : ''}>{formatCurrency(category.remaining)}</span>
                                            </span>
                                            <span>Planeado: {formatCurrency(category.planned)}</span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatCurrency } from '@/utils/formatCurrency';
import { Head } from '@inertiajs/react';

export default function Dashboard({ categories, totals }) {
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
                    <div className="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
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

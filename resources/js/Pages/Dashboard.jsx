import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Dashboard({ categories, totals }) {
    const formatCurrency = (value) =>
        '$' + Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Summary Cards */}
                    <div className="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div className="rounded-lg bg-white p-6 shadow-sm">
                            <p className="text-sm font-medium text-gray-500">Total Budget</p>
                            <p className="mt-2 text-2xl font-bold text-gray-900">
                                {formatCurrency(totals.total_budget)}
                            </p>
                        </div>
                        <div className="rounded-lg bg-white p-6 shadow-sm">
                            <p className="text-sm font-medium text-gray-500">Total Spent</p>
                            <p className="mt-2 text-2xl font-bold text-red-600">
                                {formatCurrency(totals.total_spent)}
                            </p>
                        </div>
                        <div className="rounded-lg bg-white p-6 shadow-sm">
                            <p className="text-sm font-medium text-gray-500">Planned</p>
                            <p className="mt-2 text-2xl font-bold text-yellow-600">
                                {formatCurrency(totals.total_planned)}
                            </p>
                        </div>
                        <div className="rounded-lg bg-white p-6 shadow-sm">
                            <p className="text-sm font-medium text-gray-500">Remaining</p>
                            <p className={`mt-2 text-2xl font-bold ${totals.total_remaining < 0 ? 'text-red-600' : 'text-green-600'}`}>
                                {formatCurrency(totals.total_remaining)}
                            </p>
                        </div>
                    </div>

                    {/* Per-Category Progress */}
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h3 className="mb-4 text-lg font-medium text-gray-900">
                                Category Breakdown
                            </h3>
                            {categories.length === 0 ? (
                                <p className="text-center text-gray-500">
                                    No categories yet. Create categories to see budget progress.
                                </p>
                            ) : (
                                <div className="space-y-6">
                                    {categories.map((category) => (
                                        <div key={category.id}>
                                            <div className="mb-1 flex items-center justify-between">
                                                <div className="flex items-center">
                                                    <span
                                                        className="mr-2 inline-block h-4 w-4 rounded-full"
                                                        style={{ backgroundColor: category.color }}
                                                    />
                                                    <span className="font-medium text-gray-900">
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
                                            <div className="h-4 w-full overflow-hidden rounded-full bg-gray-200">
                                                <div
                                                    className={`h-full rounded-full transition-all ${category.progress > 100 ? 'bg-red-500' : category.progress > 80 ? 'bg-yellow-500' : 'bg-indigo-500'}`}
                                                    style={{ width: `${Math.min(category.progress, 100)}%` }}
                                                />
                                            </div>
                                            <div className="mt-1 flex justify-between text-xs text-gray-500">
                                                <span>
                                                    Remaining: <span className={category.remaining < 0 ? 'font-semibold text-red-600' : ''}>{formatCurrency(category.remaining)}</span>
                                                </span>
                                                <span>Planned: {formatCurrency(category.planned)}</span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

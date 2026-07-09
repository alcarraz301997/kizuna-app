import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import DangerButton from '@/Components/DangerButton';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

const statusColors = {
    planned: 'bg-yellow-100 text-yellow-800',
    contracted: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
};

export default function Index({ expenses, categories, filters }) {
    const flash = usePage().props.flash;
    const { delete: destroy, processing } = useForm();

    const handleDelete = (id) => {
        if (confirm('Are you sure you want to delete this expense?')) {
            destroy(route('expenses.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Expenses
                    </h2>
                    <Link href={route('expenses.create')}>
                        <PrimaryButton>Add Expense</PrimaryButton>
                    </Link>
                </div>
            }
        >
            <Head title="Expenses" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {flash?.error && (
                        <div className="mb-4 rounded-md bg-red-50 p-4">
                            <p className="text-sm text-red-700">{flash.error}</p>
                        </div>
                    )}

                    <div className="mb-4">
                        <select
                            className="rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                            value={filters.category_id || ''}
                            onChange={(e) => {
                                const params = new URLSearchParams(window.location.search);
                                if (e.target.value) {
                                    params.set('category_id', e.target.value);
                                } else {
                                    params.delete('category_id');
                                }
                                const qs = params.toString();
                                window.location.href = route('expenses.index') + (qs ? '?' + qs : '');
                            }}
                        >
                            <option value="">All Categories</option>
                            {categories.map((cat) => (
                                <option key={cat.id} value={cat.id}>
                                    {cat.name}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {expenses.length === 0 ? (
                                <p className="text-center text-gray-500">
                                    No expenses yet.{' '}
                                    <Link
                                        href={route('expenses.create')}
                                        className="text-indigo-600 underline hover:text-indigo-900"
                                    >
                                        Create one
                                    </Link>
                                    .
                                </p>
                            ) : (
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Vendor
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Category
                                            </th>
                                            <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Amount
                                            </th>
                                            <th className="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Status
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Date
                                            </th>
                                            <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200">
                                        {expenses.map((expense) => (
                                            <tr key={expense.id}>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                                    {expense.vendor || '—'}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <div className="flex items-center">
                                                        <span
                                                            className="mr-2 inline-block h-3 w-3 rounded-full"
                                                            style={{ backgroundColor: expense.category.color }}
                                                        />
                                                        <span className="text-sm text-gray-700">
                                                            {expense.category.name}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                                    ${expense.amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-center">
                                                    <span className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${statusColors[expense.status] || ''}`}>
                                                        {expense.status}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                    {expense.paid_date || expense.created_at}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        <Link
                                                            href={route('expenses.edit', expense.id)}
                                                            className="text-sm text-indigo-600 hover:text-indigo-900"
                                                        >
                                                            Edit
                                                        </Link>
                                                        <DangerButton
                                                            disabled={processing}
                                                            onClick={() => handleDelete(expense.id)}
                                                        >
                                                            Delete
                                                        </DangerButton>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

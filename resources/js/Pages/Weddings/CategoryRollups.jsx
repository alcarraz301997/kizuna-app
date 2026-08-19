import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

const money = (value) => `S/. ${Number(value || 0).toFixed(2)}`;

export default function CategoryRollups({ categories = [] }) {
    const roots = categories.filter((category) => !category.parent_id);
    const children = (parentId) => categories.filter((category) => category.parent_id === parentId);
    const totals = roots.reduce((sum, category) => ({
        planned: sum.planned + Number(category.planned || 0),
        contracted: sum.contracted + Number(category.contracted || 0),
        paid: sum.paid + Number(category.paid || 0),
    }), { planned: 0, contracted: 0, paid: 0 });
    const renderCategory = (category, level = 0) => <div key={category.id} className="border-b border-gray-200/60 py-3 last:border-0" style={{ marginLeft: `${level * 1.25}rem` }}>
        <div className="grid gap-2 sm:grid-cols-[1fr_repeat(3,8rem)] sm:items-center"><div className="font-medium text-gray-800">{category.name}</div><div><span className="text-xs text-gray-500">Planeado</span><p>{money(category.planned)}</p></div><div><span className="text-xs text-gray-500">Contratado</span><p>{money(category.contracted)}</p></div><div><span className="text-xs text-gray-500">Pagado</span><p>{money(category.paid)}</p></div></div>
        {children(category.id).map((child) => renderCategory(child, level + 1))}
    </div>;

    return <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Resumen por categoría</h2>}>
        <Head title="Resumen por categoría" />
        <div className="py-12"><div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
            <div className="grid gap-4 sm:grid-cols-3"><div className="clay-card p-5"><p className="text-sm text-gray-500">Planeado</p><p className="text-xl font-bold">{money(totals.planned)}</p></div><div className="clay-card p-5"><p className="text-sm text-gray-500">Contratado</p><p className="text-xl font-bold">{money(totals.contracted)}</p></div><div className="clay-card p-5"><p className="text-sm text-gray-500">Pagado</p><p className="text-xl font-bold">{money(totals.paid)}</p></div></div>
            <section className="clay-card p-6"><h3 className="mb-4 text-lg font-semibold">Categorías</h3>{roots.length === 0 ? <p className="text-gray-500">Aún no hay categorías para resumir.</p> : roots.map((category) => renderCategory(category))}</section>
        </div></div>
    </AuthenticatedLayout>;
}

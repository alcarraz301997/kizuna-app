import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatCurrency } from '@/utils/formatCurrency';
import { Head } from '@inertiajs/react';

export default function Variance({ categories = [] }) {
    return <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Variación del presupuesto</h2>}>
        <Head title="Variación del presupuesto" />
        <div className="py-12"><div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {categories.length === 0 ? <div className="clay-card p-6 text-center text-gray-500">No hay categorías para analizar.</div> : <div className="space-y-4">{categories.map((category) => <article key={category.id} className="clay-card p-6"><h3 className="font-semibold">{category.name}</h3><div className="mt-3 grid gap-3 text-sm sm:grid-cols-3"><span>Planeado: {formatCurrency(category.planned || 0)}</span><span>Contratado: {formatCurrency(category.contracted || 0)}</span><span>Pagado: {formatCurrency(category.paid || 0)}</span></div><div className="mt-3 flex flex-wrap gap-2">{(category.alerts || []).map((alert) => <span key={alert} role="status" className="rounded-full bg-red-100 px-3 py-1 text-sm text-red-800">{alert === 'commitment_over_budget' ? 'Sobre presupuesto contratado' : 'Sobre presupuesto pagado'}</span>)}{(category.alerts || []).length === 0 && <span className="text-sm text-gray-500">Sin alertas</span>}</div></article>)}</div>}
        </div></div>
    </AuthenticatedLayout>;
}

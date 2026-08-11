import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatCurrency } from '@/utils/formatCurrency';
import { Head } from '@inertiajs/react';

export default function Forecast({ forecast }) {
    const items = [
        ...(forecast?.dated || []),
        ...(forecast?.unscheduled || []).map((item) => ({
            ...item,
            state: 'unscheduled',
            paid_to_date: item.paid_to_date ?? 0,
            balance: item.balance ?? item.contracted ?? 0,
        })),
    ];
    return <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Pronóstico de pagos</h2>}>
        <Head title="Pronóstico de pagos" />
        <div className="py-12"><div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div className="mb-6 grid gap-4 sm:grid-cols-3">{[['Contratado', forecast?.totals?.contracted], ['Pagado', forecast?.totals?.paid_to_date], ['Balance', forecast?.totals?.balance]].map(([label, value]) => <div key={label} className="clay-card p-5"><p className="text-sm text-gray-500">{label}</p><p className="mt-2 text-xl font-bold">{formatCurrency(value || 0)}</p></div>)}</div>
            {items.length === 0 ? <div className="clay-card p-6 text-center text-gray-500">No hay compromisos para pronosticar.</div> : <div className="clay-card overflow-x-auto p-6"><table className="w-full text-left text-sm"><caption className="sr-only">Compromisos de pago</caption><thead><tr><th scope="col">Estado</th><th scope="col">Contratado</th><th scope="col">Pagado</th><th scope="col">Balance</th></tr></thead><tbody>{items.map((item, index) => <tr key={item.expense_id || `${item.state}-${index}`} className="border-t"><td className="py-3">{item.state === 'past_due' ? 'Vencido' : item.state === 'unscheduled' ? 'Sin fecha' : 'Programado'}</td><td>{formatCurrency(item.contracted)}</td><td>{formatCurrency(item.paid_to_date)}</td><td>{formatCurrency(item.balance)}</td></tr>)}</tbody></table></div>}
        </div></div>
    </AuthenticatedLayout>;
}

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, useForm } from '@inertiajs/react';

export default function Show({ wedding, members = [], role }) {
    const { data, setData, post, processing, errors, reset } = useForm({ email: '', role: 'editor' });
    const isOwner = role === 'owner';

    const submit = (event) => {
        event.preventDefault();
        post(`/weddings/${wedding.id}/members`, {
            onSuccess: () => reset('email'),
        });
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">{wedding.name}</h2>}>
            <Head title={wedding.name} />
            <div className="py-12"><div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <section className="clay-card p-6" aria-labelledby="members-heading">
                    <h3 id="members-heading" className="mb-4 text-lg font-semibold">Miembros del Espacio</h3>
                    <p className="mb-4 text-sm text-gray-600">
                        Los miembros de este espacio comparten el mismo presupuesto, categorías, gastos, proveedores, mesas e invitados en tiempo real.
                    </p>
                    {members.length === 0 ? <p className="text-gray-500">Aún no hay miembros.</p> : (
                        <ul className="space-y-3">{members.map((member) => <li key={member.id} className="flex justify-between items-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                            <div>
                                <span className="font-medium text-gray-900 dark:text-gray-100">{member.name}</span>
                            </div>
                            <span className="text-xs font-semibold px-2.5 py-1 rounded-full bg-primary/10 text-primary">{member.role === 'owner' ? 'Propietario' : 'Colaborador'}</span>
                        </li>)}</ul>
                    )}
                    {isOwner ? (
                        <form onSubmit={submit} className="mt-6 flex flex-wrap items-end gap-3">
                            <label className="flex flex-col gap-1 text-sm flex-1 min-w-[240px]">
                                Correo del colaborador (pareja, wedding planner)
                                <input
                                    type="email"
                                    placeholder="pareja@ejemplo.com"
                                    aria-label="Correo del colaborador"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    className="clay-input rounded-xl"
                                    required
                                />
                            </label>
                            <PrimaryButton disabled={processing}>
                                {processing ? 'Invitando...' : 'Invitar al espacio'}
                            </PrimaryButton>
                            {errors.email && <p className="w-full text-sm text-red-600">{errors.email}</p>}
                        </form>
                    ) : <p className="mt-6 text-sm text-gray-500">Acceso de colaborador: estás compartiendo este espacio de boda.</p>}
                </section>
            </div></div>
        </AuthenticatedLayout>
    );
}

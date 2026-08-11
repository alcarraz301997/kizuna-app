import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, useForm } from '@inertiajs/react';

export default function Show({ wedding, members = [], role }) {
    const { data, setData, post, processing } = useForm({ user_id: '', role: 'editor' });
    const isOwner = role === 'owner';

    const submit = (event) => {
        event.preventDefault();
        post(`/weddings/${wedding.id}/members`);
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">{wedding.name}</h2>}>
            <Head title={wedding.name} />
            <div className="py-12"><div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <section className="clay-card p-6" aria-labelledby="members-heading">
                    <h3 id="members-heading" className="mb-4 text-lg font-semibold">Miembros</h3>
                    {members.length === 0 ? <p className="text-gray-500">Aún no hay miembros.</p> : (
                        <ul className="space-y-3">{members.map((member) => <li key={member.id} className="flex justify-between">
                            <span>{member.name}</span><span className="text-sm text-gray-500">{member.role === 'owner' ? 'Propietario' : 'Editor'}</span>
                        </li>)}</ul>
                    )}
                    {isOwner ? <form onSubmit={submit} className="mt-6 flex flex-wrap items-end gap-3">
                        <label className="flex flex-col gap-1 text-sm">ID de usuario<input aria-label="ID de usuario" value={data.user_id} onChange={(e) => setData('user_id', e.target.value)} className="clay-input rounded-xl" /></label>
                        <PrimaryButton disabled={processing}>Agregar miembro</PrimaryButton>
                    </form> : <p className="mt-6 text-sm text-gray-500">Acceso de miembro: puedes consultar este espacio.</p>}
                </section>
            </div></div>
        </AuthenticatedLayout>
    );
}

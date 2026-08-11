import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, useForm } from '@inertiajs/react';

export default function CategoryTemplates({ templates = [] }) {
    const { post, processing } = useForm();
    return <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Plantillas de categorías</h2>}>
        <Head title="Plantillas de categorías" />
        <div className="py-12"><div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {templates.length === 0 ? <div className="clay-card p-6 text-center text-gray-500">Aún no hay plantillas de categorías.</div> : <div className="grid gap-4 md:grid-cols-2">
                {templates.map((template) => <article key={template.id} className="clay-card p-6"><h3 className="text-lg font-semibold">{template.name}</h3>
                    <ul className="my-4 list-disc pl-5 text-sm text-gray-600">{(template.items || []).map((item) => <li key={item.id || item.name}>{item.name}</li>)}</ul>
                    <PrimaryButton aria-label={`Aplicar ${template.name}`} disabled={processing} onClick={() => post(`/weddings/${template.wedding_id}/category-templates/${template.id}/apply`)}>Aplicar plantilla</PrimaryButton>
                </article>)}
            </div>}
        </div></div>
    </AuthenticatedLayout>;
}

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, useForm, usePage } from '@inertiajs/react';

export default function CategoryTemplates({ templates = [], wedding: weddingProp }) {
    const { wedding: sharedWedding } = usePage().props;
    const wedding = weddingProp || sharedWedding || { id: templates[0]?.wedding_id };
    const templateForm = useForm({ name: '', items: [{ name: '', budget_limit: '' }] });
    const { post: applyTemplate, processing: applying } = useForm();

    const addItem = () => templateForm.setData('items', [...templateForm.data.items, { name: '', budget_limit: '' }]);
    const removeItem = (index) => templateForm.setData('items', templateForm.data.items.filter((_, itemIndex) => itemIndex !== index));
    const updateItem = (index, key, value) => templateForm.setData('items', templateForm.data.items.map((item, itemIndex) => itemIndex === index ? { ...item, [key]: value } : item));
    const submitTemplate = (event) => {
        event.preventDefault();
        templateForm.post(`/weddings/${wedding?.id}/category-templates`, {
            onSuccess: () => templateForm.reset(),
        });
    };

    return <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Plantillas de categorías</h2>}>
        <Head title="Plantillas de categorías" />
        <div className="py-12"><div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div className="clay-card mb-6 p-6">
                <h3 className="mb-4 text-lg font-semibold">Crear plantilla</h3>
                <form onSubmit={submitTemplate} className="space-y-4">
                    <div>
                        <InputLabel htmlFor="template_name" value="Nombre de la plantilla" />
                        <TextInput id="template_name" className="mt-1 block w-full" value={templateForm.data.name} onChange={(event) => templateForm.setData('name', event.target.value)} required />
                        <InputError className="mt-2" message={templateForm.errors.name} />
                    </div>
                    <div className="space-y-3">
                        <div className="flex items-center justify-between"><h4 className="font-semibold">Categorías</h4><button type="button" className="text-sm font-semibold text-indigo-600" onClick={addItem}>Agregar categoría</button></div>
                        {templateForm.data.items.map((item, index) => <div key={index} className="grid gap-3 sm:grid-cols-[1fr_10rem_auto]">
                            <div><InputLabel htmlFor={`template_item_${index}`} value="Nombre" /><TextInput id={`template_item_${index}`} className="mt-1 block w-full" value={item.name} onChange={(event) => updateItem(index, 'name', event.target.value)} required /><InputError className="mt-1" message={templateForm.errors[`items.${index}.name`]} /></div>
                            <div><InputLabel htmlFor={`template_budget_${index}`} value="Presupuesto" /><TextInput id={`template_budget_${index}`} type="number" min="0" step="0.01" className="mt-1 block w-full" value={item.budget_limit} onChange={(event) => updateItem(index, 'budget_limit', event.target.value)} /></div>
                            <button type="button" className="self-end pb-2 text-sm text-red-600 disabled:text-gray-400" onClick={() => removeItem(index)} disabled={templateForm.data.items.length === 1}>Quitar</button>
                        </div>)}
                    </div>
                    <InputError message={templateForm.errors.items} />
                    <PrimaryButton disabled={templateForm.processing || !wedding?.id}>Guardar plantilla</PrimaryButton>
                </form>
            </div>
            {templates.length === 0 ? <div className="clay-card p-6 text-center text-gray-500">Aún no hay plantillas de categorías.</div> : <div className="grid gap-4 md:grid-cols-2">
                {templates.map((template) => <article key={template.id} className="clay-card p-6"><h3 className="text-lg font-semibold">{template.name}</h3>
                    <ul className="my-4 list-disc pl-5 text-sm text-gray-600">{(template.items || []).map((item) => <li key={item.id || item.name}>{item.name}</li>)}</ul>
                    <PrimaryButton aria-label={`Aplicar ${template.name}`} disabled={applying} onClick={() => applyTemplate(`/weddings/${template.wedding_id || wedding?.id}/category-templates/${template.id}/apply`)}>Aplicar plantilla</PrimaryButton>
                </article>)}
            </div>}
        </div></div>
    </AuthenticatedLayout>;
}

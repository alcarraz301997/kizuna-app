import { Link } from '@inertiajs/react';

export default function PlanningNav({ wedding }) {
    if (!wedding) return null;

    const links = [
        ['Espacio de trabajo', `/weddings/${wedding.id}`],
        ['Plantillas', `/weddings/${wedding.id}/category-templates`],
        ['Pronóstico', `/weddings/${wedding.id}/forecast`],
        ['Variación', `/weddings/${wedding.id}/variance`],
    ];

    return (
        <div className="flex flex-wrap gap-2" aria-label="Planificación de boda">
            {links.map(([label, href]) => (
                <Link key={href} href={href} className="clay-nav-link rounded-xl px-3 py-2 text-sm">
                    {label}
                </Link>
            ))}
        </div>
    );
}

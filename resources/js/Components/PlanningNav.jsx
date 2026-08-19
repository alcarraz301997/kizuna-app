import Dropdown from '@/Components/Dropdown';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';

export default function PlanningNav({
    wedding,
    variant = 'desktop',
    onNavigate,
}) {
    if (!wedding) return null;

    const links = [
        ['Espacio de trabajo', `/weddings/${wedding.id}`],
        ['Plantillas', `/weddings/${wedding.id}/category-templates`],
        ['Resumen por categoría', `/weddings/${wedding.id}/category-rollups`],
        ['Pronóstico', `/weddings/${wedding.id}/forecast`],
        ['Variación', `/weddings/${wedding.id}/variance`],
    ];

    if (variant === 'mobile') {
        return (
            <section
                className="border-t border-white/40 pt-3"
                aria-labelledby="planning-navigation-label"
            >
                <h2
                    id="planning-navigation-label"
                    className="px-4 pb-2 text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                    Planificación
                </h2>
                <div className="space-y-1">
                    {links.map(([label, href]) => (
                        <ResponsiveNavLink
                            key={href}
                            href={href}
                            onClick={onNavigate}
                        >
                            {label}
                        </ResponsiveNavLink>
                    ))}
                </div>
            </section>
        );
    }

    return (
        <Dropdown>
            <Dropdown.Trigger>
                {({ open }) => (
                    <button
                        type="button"
                        className="clay-nav-link inline-flex items-center gap-1 rounded-xl px-3 py-2 text-sm font-medium"
                        aria-haspopup="true"
                        aria-expanded={open}
                    >
                        Planificación
                        <svg
                            className="h-4 w-4"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                fillRule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clipRule="evenodd"
                            />
                        </svg>
                    </button>
                )}
            </Dropdown.Trigger>
            <Dropdown.Content align="left">
                {links.map(([label, href]) => (
                    <Dropdown.Link key={href} href={href}>
                        {label}
                    </Dropdown.Link>
                ))}
            </Dropdown.Content>
        </Dropdown>
    );
}

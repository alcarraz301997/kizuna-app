import { describe, expect, it, vi } from 'vitest';
import React from 'react';
import { fireEvent, render, screen } from '@testing-library/react';
import Workspace from '@/Pages/Weddings/Show';
import Templates from '@/Pages/Weddings/CategoryTemplates';
import CategoryRollups from '@/Pages/Weddings/CategoryRollups';
import Forecast from '@/Pages/Weddings/Forecast';
import Variance from '@/Pages/Weddings/Variance';
import PlanningNav from '@/Components/PlanningNav';

const inertia = vi.hoisted(() => ({ post: vi.fn(), wedding: { id: 9, name: 'Nuestra boda' } }));

vi.mock('@inertiajs/react', async () => ({
    Head: () => null,
    Link: ({ children, onClick, ...props }) => (
        <a
            {...props}
            onClick={(event) => {
                event.preventDefault();
                onClick?.(event);
            }}
        >
            {children}
        </a>
    ),
    usePage: () => ({ props: { wedding: inertia.wedding } }),
    useForm: (initialData = {}) => {
        const [data, setDataState] = React.useState(initialData);
        return {
            data,
            setData: (key, value) => setDataState((current) => ({ ...current, [key]: value })),
            post: inertia.post,
            patch: vi.fn(),
            reset: vi.fn(),
            errors: {},
            processing: false,
        };
    },
}));

vi.mock('@/Layouts/AuthenticatedLayout', () => ({
    default: ({ header, children }) => <main><header>{header}</header>{children}</main>,
}));

describe('wedding planning pages', () => {
    it('shows owner member management but keeps editor access read-only', () => {
        const members = [
            { id: 1, name: 'Owner', email: 'owner@example.test', role: 'owner' },
            { id: 2, name: 'Editor', email: 'editor@example.test', role: 'editor' },
        ];

        const { rerender } = render(<Workspace wedding={{ id: 1, name: 'Our Wedding' }} members={members} role="owner" />);
        expect(screen.getByRole('heading', { name: 'Our Wedding' })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /agregar miembro/i })).toBeInTheDocument();
        expect(screen.getAllByText('Editor').length).toBeGreaterThan(0);

        rerender(<Workspace wedding={{ id: 1, name: 'Our Wedding' }} members={members} role="editor" />);
        expect(screen.queryByRole('button', { name: /agregar miembro/i })).not.toBeInTheDocument();
        expect(screen.getByText(/acceso de miembro/i)).toBeInTheDocument();
    });

    it('renders template actions and an accessible empty state', () => {
        const { rerender } = render(<Templates templates={[]} />);
        expect(screen.getByText(/aún no hay plantillas/i)).toBeInTheDocument();

        rerender(<Templates templates={[{ id: 1, name: 'Reception', items: [{ name: 'Venue' }] }]} />);
        expect(screen.getByText('Reception')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /aplicar reception/i })).toBeInTheDocument();
        expect(screen.getByText('Venue')).toBeInTheDocument();
    });

    it('submits a template with at least one item', () => {
        inertia.post.mockClear();
        render(<Templates templates={[]} />);

        fireEvent.change(screen.getByLabelText('Nombre de la plantilla'), { target: { value: 'Ceremonia' } });
        fireEvent.change(screen.getByLabelText('Nombre', { selector: '#template_item_0' }), { target: { value: 'Lugar' } });
        fireEvent.click(screen.getByRole('button', { name: 'Guardar plantilla' }));

        expect(inertia.post).toHaveBeenCalledWith('/weddings/9/category-templates', expect.anything());
    });

    it('renders category rollups as a hierarchy with financial summaries', () => {
        render(<CategoryRollups categories={[
            { id: 1, name: 'Recepción', parent_id: null, planned: 1000, contracted: 1200, paid: 500 },
            { id: 2, name: 'Catering', parent_id: 1, planned: 1000, contracted: 1200, paid: 500 },
        ]} />);

        expect(screen.getByRole('heading', { name: 'Resumen por categoría' })).toBeInTheDocument();
        expect(screen.getByText('Recepción')).toBeInTheDocument();
        expect(screen.getByText('Catering')).toBeInTheDocument();
        expect(screen.getAllByText('S/. 1200.00')).toHaveLength(3);
    });

    it('keeps dated, unscheduled, and empty forecast states distinct', () => {
        const { rerender } = render(<Forecast forecast={{ dated: [{ state: 'past_due', contracted: 1000, paid_to_date: 250, balance: 750 }], unscheduled: [], totals: { contracted: 1000, paid_to_date: 250, balance: 750 } }} />);
        expect(screen.getByText(/vencido/i)).toBeInTheDocument();
        expect(screen.getAllByText('S/. 750.00').length).toBeGreaterThan(0);

        rerender(<Forecast forecast={{ dated: [], unscheduled: [{ contracted: 300 }], totals: { contracted: 300, paid_to_date: 0, balance: 300 } }} />);
        expect(screen.getByText(/sin fecha/i)).toBeInTheDocument();

        rerender(<Forecast forecast={{ dated: [], unscheduled: [], totals: { contracted: 0, paid_to_date: 0, balance: 0 } }} />);
        expect(screen.getByText(/no hay compromisos/i)).toBeInTheDocument();
    });

    it('shows distinct financial variances and an empty alert state', () => {
        render(<Variance categories={[
            { id: 1, name: 'Venue', planned: 100, contracted: 150, paid: 120, commitment_variance: 50, paid_variance: 20, alerts: ['commitment_over_budget', 'paid_over_budget'] },
            { id: 2, name: 'Empty', planned: null, contracted: null, paid: null, commitment_variance: null, paid_variance: null, alerts: [] },
        ]} />);
        expect(screen.getByText('Venue')).toBeInTheDocument();
        expect(screen.getByText(/sobre presupuesto contratado/i)).toBeInTheDocument();
        expect(screen.getByText(/sobre presupuesto pagado/i)).toBeInTheDocument();
        expect(screen.getByText('Empty')).toBeInTheDocument();
        expect(screen.getByText(/sin alertas/i)).toBeInTheDocument();
    });

    it('hides planning navigation without an admitted wedding', () => {
        const { container } = render(<PlanningNav wedding={null} />);

        expect(container).toBeEmptyDOMElement();
        expect(
            screen.queryByRole('button', { name: /planificación/i }),
        ).not.toBeInTheDocument();
    });

    it('shows one compact desktop trigger and its destinations when opened', () => {
        render(<PlanningNav wedding={{ id: 7, name: 'Our Wedding' }} />);

        const trigger = screen.getByRole('button', {
            name: /planificación/i,
        });
        expect(trigger).toHaveAttribute('aria-expanded', 'false');
        expect(
            screen.queryByRole('link', { name: /espacio de trabajo/i }),
        ).not.toBeInTheDocument();

        fireEvent.click(trigger);

        expect(trigger).toHaveAttribute('aria-expanded', 'true');
        expect(
            screen.getByRole('link', { name: /espacio de trabajo/i }),
        ).toHaveAttribute('href', '/weddings/7');
        expect(
            screen.getByRole('link', { name: /plantillas/i }),
        ).toHaveAttribute('href', '/weddings/7/category-templates');
        expect(
            screen.getByRole('link', { name: /resumen por categoría/i }),
        ).toHaveAttribute('href', '/weddings/7/category-rollups');
        expect(
            screen.getByRole('link', { name: /pronóstico/i }),
        ).toHaveAttribute('href', '/weddings/7/forecast');
        expect(
            screen.getByRole('link', { name: /variación/i }),
        ).toHaveAttribute('href', '/weddings/7/variance');
    });

    it('shows full-width mobile destinations and closes after selection', () => {
        const closeNav = vi.fn();

        render(
            <PlanningNav
                wedding={{ id: 7, name: 'Our Wedding' }}
                variant="mobile"
                onNavigate={closeNav}
            />,
        );

        expect(
            screen.getByRole('heading', { name: 'Planificación' }),
        ).toBeInTheDocument();

        const destinations = screen.getAllByRole('link');
        expect(destinations).toHaveLength(5);
        destinations.forEach((destination) =>
            expect(destination).toHaveClass('w-full'),
        );

        fireEvent.click(screen.getByRole('link', { name: /pronóstico/i }));
        expect(closeNav).toHaveBeenCalledOnce();
    });
});

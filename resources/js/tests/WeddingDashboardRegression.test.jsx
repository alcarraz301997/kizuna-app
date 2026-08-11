import { beforeEach, describe, expect, it, vi } from 'vitest';
import { fireEvent, render, screen } from '@testing-library/react';
import Dashboard from '@/Pages/Dashboard';

const inertia = vi.hoisted(() => ({
    post: vi.fn(),
    wedding: null,
}));

vi.mock('@inertiajs/react', async () => {
    const React = await vi.importActual('react');

    return {
        Head: () => null,
        Link: ({ href, children, ...props }) => <a href={href} {...props}>{children}</a>,
        usePage: () => ({ props: { wedding: inertia.wedding } }),
        useForm: (initialData) => {
            const [data, setData] = React.useState(initialData);

            return {
                data,
                setData: (key, value) => setData((current) => ({ ...current, [key]: value })),
                post: inertia.post,
                errors: {},
                processing: false,
            };
        },
    };
});

vi.mock('@/Layouts/AuthenticatedLayout', () => ({
    default: ({ children }) => <main>{children}</main>,
}));

describe('dashboard financial regression', () => {
    beforeEach(() => {
        inertia.post.mockClear();
        inertia.wedding = null;
    });

    it('lets users without a wedding create their workspace', () => {
        render(<Dashboard categories={[]} totals={{}} />);

        fireEvent.change(screen.getByLabelText('Nombre de la boda'), {
            target: { value: 'Nuestra boda' },
        });
        fireEvent.click(screen.getByRole('button', { name: 'Crear espacio de boda' }));

        expect(screen.getByText('Comienza a planificar tu boda')).toBeInTheDocument();
        expect(inertia.post).toHaveBeenCalledWith('/weddings');
    });

    it('links users with a wedding to its workspace', () => {
        inertia.wedding = { id: 7, name: 'Nuestra boda' };

        render(<Dashboard categories={[]} totals={{}} />);

        expect(screen.queryByLabelText('Nombre de la boda')).not.toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'Ir al espacio de trabajo' }))
            .toHaveAttribute('href', '/weddings/7');
    });

    it('shows planned, contracted, and paid totals independently', () => {
        render(<Dashboard
            categories={[]}
            totals={{
                total_budget: 1000,
                total_spent: 300,
                total_planned: 250,
                total_contracted: 400,
                total_paid: 300,
                total_remaining: 700,
            }}
        />);

        expect(screen.getByText('Planeado')).toBeInTheDocument();
        expect(screen.getByText('Contratado')).toBeInTheDocument();
        expect(screen.getByText('Pagado')).toBeInTheDocument();
    });
});

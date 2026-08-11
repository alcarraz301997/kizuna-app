import { describe, expect, it, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import Dashboard from '@/Pages/Dashboard';

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
}));

vi.mock('@/Layouts/AuthenticatedLayout', () => ({
    default: ({ children }) => <main>{children}</main>,
}));

describe('dashboard financial regression', () => {
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

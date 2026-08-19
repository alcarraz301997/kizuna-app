import { describe, expect, it, vi } from 'vitest';
import { fireEvent, render, screen } from '@testing-library/react';
import Edit from '@/Pages/Expenses/Edit';

globalThis.route = vi.fn(() => '/expenses');

const inertia = vi.hoisted(() => ({ post: vi.fn(), patch: vi.fn() }));

vi.mock('@inertiajs/react', async () => {
    const React = await vi.importActual('react');
    return {
        Head: () => null,
        Link: ({ children, ...props }) => <a {...props}>{children}</a>,
        useForm: (initialData = {}) => {
            const [data, setDataState] = React.useState(initialData);
            return {
                data,
                setData: (key, value) => setDataState((current) => ({ ...current, [key]: value })),
                post: inertia.post,
                patch: inertia.patch,
                put: vi.fn(),
                reset: vi.fn(),
                errors: {},
                processing: false,
            };
        },
    };
});

vi.mock('@/Layouts/AuthenticatedLayout', () => ({ default: ({ children }) => <main>{children}</main> }));
vi.mock('@/Components/ReceiptPreview', () => ({ default: () => null }));
vi.mock('@/Components/SplitForm', () => ({ default: () => null }));

const props = {
    expense: { id: 4, wedding_id: 8, category_id: 1, amount: 1000, vendor: '', vendor_id: null, status: 'planned', paid_date: '', notes: '', split: null },
    categories: [],
    vendors: [],
    receipts: [],
    statuses: [{ value: 'planned', label: 'Planned' }],
    maxReceipts: 5,
    commitment: { planned_amount: 1000, contracted_amount: 900, paid_to_date: 200, balance: 700, due_date: '2026-09-01' },
};

describe('expense commitment controls', () => {
    it('submits commitment updates to the wedding-scoped endpoint', () => {
        inertia.patch.mockClear();
        render(<Edit {...props} />);

        fireEvent.change(screen.getByLabelText('Monto contratado'), { target: { value: '950' } });
        fireEvent.click(screen.getByRole('button', { name: 'Guardar compromiso' }));

        expect(inertia.patch).toHaveBeenCalledWith('/weddings/8/expenses/4/commitment', expect.anything());
    });

    it('submits a payment and shows the current paid balance', () => {
        inertia.post.mockClear();
        render(<Edit {...props} />);

        expect(screen.getByText('S/. 200.00')).toBeInTheDocument();
        expect(screen.getByText('S/. 700.00')).toBeInTheDocument();
        fireEvent.change(screen.getByLabelText('Nuevo pago'), { target: { value: '125' } });
        fireEvent.click(screen.getByRole('button', { name: 'Registrar pago' }));

        expect(inertia.post).toHaveBeenCalledWith('/weddings/8/expenses/4/payments', expect.anything());
    });
});

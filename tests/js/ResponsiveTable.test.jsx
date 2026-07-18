import { describe, it, expect, beforeEach, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import ResponsiveTable from '@/Components/ResponsiveTable';

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email' },
    {
        key: 'status',
        label: 'Status',
        render: (row) => <span data-testid={`status-${row.id}`}>{row.status.toUpperCase()}</span>,
    },
];

const rows = [
    { id: 1, name: 'Alice', email: 'alice@test.com', status: 'active' },
    { id: 2, name: 'Bob', email: 'bob@test.com', status: 'inactive' },
];

describe('ResponsiveTable', () => {
    it('renders empty message when no rows', () => {
        render(
            <ResponsiveTable
                columns={columns}
                rows={[]}
                rowKey={(row) => row.id}
                emptyMessage="No data available"
            />,
        );

        expect(screen.getByText(/No data available/)).toBeInTheDocument();
    });

    it('renders empty message with link', () => {
        render(
            <ResponsiveTable
                columns={columns}
                rows={[]}
                rowKey={(row) => row.id}
                emptyMessage="No items."
                emptyLinkHref="/create"
                emptyLinkText="Add one"
            />,
        );

        expect(screen.getByText(/No items/)).toBeInTheDocument();
        expect(screen.getByRole('link', { name: /add one/i })).toHaveAttribute('href', '/create');
    });

    it('renders column headers in desktop view', () => {
        render(
            <ResponsiveTable
                columns={columns}
                rows={rows}
                rowKey={(row) => row.id}
            />,
        );

        // jsdom renders both views; headers appear in <th> and card labels
        expect(screen.getAllByText('Name').length).toBeGreaterThanOrEqual(1);
        expect(screen.getAllByText('Email').length).toBeGreaterThanOrEqual(1);
        expect(screen.getAllByText('Status').length).toBeGreaterThanOrEqual(1);
    });

    it('renders data rows', () => {
        render(
            <ResponsiveTable
                columns={columns}
                rows={rows}
                rowKey={(row) => row.id}
            />,
        );

        expect(screen.getAllByText('Alice').length).toBeGreaterThanOrEqual(1);
        expect(screen.getAllByText('Bob').length).toBeGreaterThanOrEqual(1);
        expect(screen.getAllByText('alice@test.com').length).toBeGreaterThanOrEqual(1);
    });

    it('renders custom cell content via render function', () => {
        render(
            <ResponsiveTable
                columns={columns}
                rows={rows}
                rowKey={(row) => row.id}
            />,
        );

        // Custom renders appear in both desktop and mobile views
        const statusEls = screen.getAllByTestId('status-1');
        expect(statusEls.length).toBeGreaterThanOrEqual(1);
        expect(statusEls[0]).toHaveTextContent('ACTIVE');

        const status2Els = screen.getAllByTestId('status-2');
        expect(status2Els.length).toBeGreaterThanOrEqual(1);
        expect(status2Els[0]).toHaveTextContent('INACTIVE');
    });

    it('renders action buttons', () => {
        const actions = (row) => (
            <button data-testid={`edit-${row.id}`}>Edit {row.name}</button>
        );

        render(
            <ResponsiveTable
                columns={columns}
                rows={rows.slice(0, 1)}
                rowKey={(row) => row.id}
                actions={actions}
            />,
        );

        const editEls = screen.getAllByTestId('edit-1');
        expect(editEls.length).toBeGreaterThanOrEqual(1);
        expect(editEls[0]).toHaveTextContent('Edit Alice');
    });

    it('renders mobile cards view with column labels', () => {
        render(
            <ResponsiveTable
                columns={columns}
                rows={rows}
                rowKey={(row) => row.id}
            />,
        );

        // Card view labels appear alongside table headers — we should have at least 3 per row
        // 2 rows × 3 columns = 6 min, plus 3 table headers = we check count >= 2 per column
        const nameEls = screen.getAllByText('Name');
        // Table header (1) + 2 card labels = at least 3
        expect(nameEls.length).toBeGreaterThanOrEqual(3);

        expect(screen.getAllByText('Alice').length).toBeGreaterThanOrEqual(1);
        expect(screen.getAllByText('Bob').length).toBeGreaterThanOrEqual(1);
    });

    it('applies custom className', () => {
        render(
            <ResponsiveTable
                columns={columns}
                rows={rows}
                rowKey={(row) => row.id}
                className="my-custom-class"
            />,
        );

        const wrapper = document.querySelector('.my-custom-class');
        expect(wrapper).toBeInTheDocument();
    });
});

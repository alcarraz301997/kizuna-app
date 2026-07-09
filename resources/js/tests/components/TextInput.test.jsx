import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import TextInput from '@/Components/TextInput';

describe('TextInput', () => {
    it('renders an input element', () => {
        render(<TextInput placeholder="Enter name" />);
        expect(screen.getByPlaceholderText('Enter name')).toBeInTheDocument();
    });

    it('uses text type by default', () => {
        render(<TextInput data-testid="input" />);
        const input = screen.getByTestId('input');
        expect(input).toHaveAttribute('type', 'text');
    });

    it('accepts a custom type', () => {
        render(<TextInput type="email" data-testid="input" />);
        const input = screen.getByTestId('input');
        expect(input).toHaveAttribute('type', 'email');
    });
});

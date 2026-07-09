import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import PrimaryButton from '@/Components/PrimaryButton';

describe('PrimaryButton', () => {
    it('renders children text', () => {
        render(<PrimaryButton>Save</PrimaryButton>);
        expect(screen.getByRole('button', { name: /save/i })).toBeInTheDocument();
    });

    it('is disabled when disabled prop is true', () => {
        render(<PrimaryButton disabled>Submit</PrimaryButton>);
        expect(screen.getByRole('button')).toBeDisabled();
    });

    it('applies custom className', () => {
        render(<PrimaryButton className="extra-class">Click</PrimaryButton>);
        expect(screen.getByRole('button').className).toContain('extra-class');
    });
});

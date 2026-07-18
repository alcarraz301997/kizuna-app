import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import DangerButton from '@/Components/DangerButton';
import TextInput from '@/Components/TextInput';
import Checkbox from '@/Components/Checkbox';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';

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

    it('has minimum touch target height', () => {
        render(<PrimaryButton>Tap</PrimaryButton>);
        expect(screen.getByRole('button').className).toContain('min-h-touch');
    });
});

describe('SecondaryButton', () => {
    it('renders with type button by default', () => {
        render(<SecondaryButton>Cancel</SecondaryButton>);
        const btn = screen.getByRole('button', { name: /cancel/i });
        expect(btn).toBeInTheDocument();
        expect(btn).toHaveAttribute('type', 'button');
    });

    it('applies clay-btn-secondary class', () => {
        render(<SecondaryButton>Cancel</SecondaryButton>);
        expect(screen.getByRole('button').className).toContain('clay-btn-secondary');
    });
});

describe('DangerButton', () => {
    it('renders children text', () => {
        render(<DangerButton>Delete</DangerButton>);
        expect(screen.getByRole('button', { name: /delete/i })).toBeInTheDocument();
    });

    it('applies clay-btn-danger class', () => {
        render(<DangerButton>Delete</DangerButton>);
        expect(screen.getByRole('button').className).toContain('clay-btn-danger');
    });
});

describe('TextInput', () => {
    it('renders input element', () => {
        render(<TextInput placeholder="Enter text" />);
        expect(screen.getByPlaceholderText('Enter text')).toBeInTheDocument();
    });

    it('applies clay-input class', () => {
        render(<TextInput />);
        const input = screen.getByRole('textbox');
        expect(input.className).toContain('clay-input');
    });

    it('has minimum touch target', () => {
        render(<TextInput />);
        expect(screen.getByRole('textbox').className).toContain('min-h-touch');
    });
});

describe('Checkbox', () => {
    it('renders checkbox input', () => {
        render(<Checkbox />);
        const cb = screen.getByRole('checkbox');
        expect(cb).toBeInTheDocument();
        expect(cb).toHaveAttribute('type', 'checkbox');
    });

    it('applies min touch target', () => {
        render(<Checkbox />);
        expect(screen.getByRole('checkbox').className).toContain('min-h-touch');
    });
});

describe('InputLabel', () => {
    it('renders label text', () => {
        render(<InputLabel value="Email" />);
        expect(screen.getByText('Email')).toBeInTheDocument();
    });

    it('renders children when no value', () => {
        render(<InputLabel><span>Custom</span></InputLabel>);
        expect(screen.getByText('Custom')).toBeInTheDocument();
    });
});

describe('InputError', () => {
    it('renders error message', () => {
        render(<InputError message="Required field" />);
        expect(screen.getByText('Required field')).toBeInTheDocument();
    });

    it('returns null when no message', () => {
        const { container } = render(<InputError message="" />);
        expect(container.firstChild).toBeNull();
    });
});

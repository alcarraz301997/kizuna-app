import { describe, it, expect, beforeEach, vi } from 'vitest';
import { render, screen, act } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { ThemeProvider, useTheme, themes } from '@/Providers/ThemeProvider';

// Clean localStorage before each test
beforeEach(() => {
    localStorage.clear();
    // Reset document class
    document.documentElement.className = '';
});

// Test helper: a consumer component to inspect context values
function ThemeConsumer() {
    const ctx = useTheme();
    return (
        <div>
            <span data-testid="theme-value">{ctx.theme}</span>
            <span data-testid="resolved-value">{ctx.resolvedTheme ?? 'null'}</span>
            <button
                data-testid="set-ocean"
                onClick={() => ctx.setTheme('ocean')}
            >
                Set Ocean
            </button>
            <button
                data-testid="set-invalid"
                onClick={() => ctx.setTheme('invalid-theme')}
            >
                Set Invalid
            </button>
            <button
                data-testid="cycle-theme"
                onClick={() => ctx.cycleTheme()}
            >
                Cycle
            </button>
        </div>
    );
}

describe('ThemeProvider', () => {
    it('initializes with default theme when no preference is stored', () => {
        render(
            <ThemeProvider>
                <ThemeConsumer />
            </ThemeProvider>,
        );

        expect(screen.getByTestId('theme-value').textContent).toBe('default');
    });

    it('reads stored theme on initialization', () => {
        localStorage.setItem('kizuna-theme', 'ocean');

        render(
            <ThemeProvider>
                <ThemeConsumer />
            </ThemeProvider>,
        );

        expect(screen.getByTestId('theme-value').textContent).toBe('ocean');
    });

    it('falls back to default when stored theme is invalid', () => {
        localStorage.setItem('kizuna-theme', 'nonexistent');

        render(
            <ThemeProvider>
                <ThemeConsumer />
            </ThemeProvider>,
        );

        expect(screen.getByTestId('theme-value').textContent).toBe('default');
    });

    it('setTheme changes theme and persists to localStorage', async () => {
        const user = userEvent.setup();

        render(
            <ThemeProvider>
                <ThemeConsumer />
            </ThemeProvider>,
        );

        await user.click(screen.getByTestId('set-ocean'));

        expect(screen.getByTestId('theme-value').textContent).toBe('ocean');
        expect(localStorage.getItem('kizuna-theme')).toBe('ocean');
    });

    it('setTheme ignores invalid theme names', async () => {
        const user = userEvent.setup();

        render(
            <ThemeProvider>
                <ThemeConsumer />
            </ThemeProvider>,
        );

        await user.click(screen.getByTestId('set-invalid'));

        // Should still be default
        expect(screen.getByTestId('theme-value').textContent).toBe('default');
    });

    it('cycleTheme cycles through all themes', async () => {
        const user = userEvent.setup();

        render(
            <ThemeProvider>
                <ThemeConsumer />
            </ThemeProvider>,
        );

        const get = () => screen.getByTestId('theme-value').textContent;

        expect(get()).toBe('default');

        await user.click(screen.getByTestId('cycle-theme'));
        expect(get()).toBe('ocean');

        await user.click(screen.getByTestId('cycle-theme'));
        expect(get()).toBe('forest');

        await user.click(screen.getByTestId('cycle-theme'));
        expect(get()).toBe('sunset');

        await user.click(screen.getByTestId('cycle-theme'));
        expect(get()).toBe('default'); // wraps around
    });

    it('applies theme class to document.documentElement', () => {
        render(
            <ThemeProvider>
                <ThemeConsumer />
            </ThemeProvider>,
        );

        // After mount, the class should be applied
        expect(document.documentElement.classList.contains('theme-default')).toBe(true);
    });

    it('updates document class when theme changes', async () => {
        const user = userEvent.setup();

        render(
            <ThemeProvider>
                <ThemeConsumer />
            </ThemeProvider>,
        );

        await user.click(screen.getByTestId('set-ocean'));

        expect(document.documentElement.classList.contains('theme-ocean')).toBe(true);
        expect(document.documentElement.classList.contains('theme-default')).toBe(false);
    });

    it('throws error when useTheme is used outside provider', () => {
        // Suppress console.error for expected error boundary behavior
        const spy = vi.spyOn(console, 'error').mockImplementation(() => {});

        function BadConsumer() {
            useTheme();
            return null;
        }

        expect(() => render(<BadConsumer />)).toThrow(
            'useTheme must be used within a ThemeProvider',
        );

        spy.mockRestore();
    });
});

describe('ThemeToggle', () => {
    it('renders and cycles theme on click', async () => {
        const user = userEvent.setup();
        const ThemeToggle = (await import('@/Components/ThemeToggle')).default;

        render(
            <ThemeProvider>
                <ThemeToggle />
                <ThemeConsumer />
            </ThemeProvider>,
        );

        const toggle = screen.getByRole('button', { name: /tema actual/i });
        expect(toggle).toBeInTheDocument();

        await user.click(toggle);
        expect(screen.getByTestId('theme-value').textContent).toBe('ocean');

        await user.click(toggle);
        expect(screen.getByTestId('theme-value').textContent).toBe('forest');
    });
});

import { createContext, useCallback, useContext, useEffect, useState } from 'react';

export const themes = ['default', 'ocean', 'forest', 'sunset', 'dark'];

const THEME_STORAGE_KEY = 'kizuna-theme';
const DEFAULT_THEME = 'default';
const THEME_CLASS_PREFIX = 'theme-';

const ThemeContext = createContext(undefined);

function isValidTheme(value) {
    return themes.includes(value);
}

function readStoredTheme() {
    try {
        const stored = localStorage.getItem(THEME_STORAGE_KEY);
        if (stored && isValidTheme(stored)) {
            return stored;
        }
    } catch {
        // localStorage unavailable — fall back to default
    }
    return DEFAULT_THEME;
}

function applyThemeClass(theme) {
    const root = document.documentElement;
    // Remove any existing theme class
    root.classList.forEach((cls) => {
        if (cls.startsWith(THEME_CLASS_PREFIX)) {
            root.classList.remove(cls);
        }
    });

    // Add the new theme class if not default (default is :root styles)
    const themeClass = `${THEME_CLASS_PREFIX}${theme}`;
    root.classList.add(themeClass);
}

export function ThemeProvider({ children }) {
    const [theme, setThemeState] = useState(() => {
        // Lazy initialization: read localStorage synchronously
        return readStoredTheme();
    });

    const [mounted, setMounted] = useState(false);

    // Apply theme class on mount and when theme changes
    useEffect(() => {
        setMounted(true);
        applyThemeClass(theme);
    }, [theme]);

    const setTheme = useCallback((nextTheme) => {
        if (!isValidTheme(nextTheme)) return;
        setThemeState(nextTheme);
        try {
            localStorage.setItem(THEME_STORAGE_KEY, nextTheme);
        } catch {
            // Storage unavailable
        }
    }, []);

    const cycleTheme = useCallback(() => {
        setThemeState((current) => {
            const idx = themes.indexOf(current);
            const next = themes[(idx + 1) % themes.length];
            try {
                localStorage.setItem(THEME_STORAGE_KEY, next);
            } catch {
                // Storage unavailable
            }
            return next;
        });
    }, []);

    const value = {
        theme,
        setTheme,
        cycleTheme,
        themes,
        resolvedTheme: mounted ? theme : null,
    };

    return (
        <ThemeContext.Provider value={value}>
            {children}
        </ThemeContext.Provider>
    );
}

export function useTheme() {
    const context = useContext(ThemeContext);
    if (context === undefined) {
        throw new Error('useTheme must be used within a ThemeProvider');
    }
    return context;
}

export default ThemeProvider;

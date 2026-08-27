import { useTheme } from '@/Providers/ThemeProvider';

const themeLabels = {
    default: 'Default',
    ocean: 'Ocean',
    forest: 'Forest',
    sunset: 'Sunset',
    dark: 'Oscuro',
};

const themeIcons = {
    default: '●',
    ocean: '●',
    forest: '●',
    sunset: '●',
    dark: '●',
};

const themeColors = {
    default: 'bg-indigo-500',
    ocean: 'bg-sky-500',
    forest: 'bg-emerald-500',
    sunset: 'bg-orange-500',
    dark: 'bg-slate-700 border border-slate-500',
};

export default function ThemeToggle({ className = '' }) {
    const { theme, cycleTheme } = useTheme();

    return (
        <button
            type="button"
            onClick={cycleTheme}
            className={`clay-btn inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition duration-150 ease-in-out focus:outline-none ${className}`}
            aria-label={`Tema actual: ${themeLabels[theme] || theme}. Click para cambiar.`}
            title={`Cambiar tema (${themeLabels[theme] || theme})`}
        >
            <span
                className={`inline-block h-4 w-4 rounded-full ${themeColors[theme] || 'bg-gray-400'}`}
                aria-hidden="true"
            />
            <span className="hidden sm:inline text-secondary">
                {themeLabels[theme] || theme}
            </span>
        </button>
    );
}

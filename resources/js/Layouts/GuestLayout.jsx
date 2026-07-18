import ApplicationLogo from '@/Components/ApplicationLogo';
import ThemeToggle from '@/Components/ThemeToggle';
import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="clay-bg flex min-h-screen flex-col items-center justify-center pt-6 sm:pt-0">
            <div className="relative">
                <div className="clay-blob bg-indigo-300/40 -left-20 -top-20 h-40 w-40" style={{ backgroundColor: `rgb(var(--blob-1))` }} />
                <div className="clay-blob bg-rose-300/40 -right-16 -bottom-16 h-32 w-32" style={{ backgroundColor: `rgb(var(--blob-2))` }} />
                <Link href="/">
                    <ApplicationLogo className="relative z-10 h-20 w-20 fill-current text-primary" />
                </Link>
            </div>

            <div className="clay-card mt-6 w-full px-8 py-6 sm:max-w-md">
                {children}
            </div>

            <div className="mt-6">
                <ThemeToggle />
            </div>
        </div>
    );
}

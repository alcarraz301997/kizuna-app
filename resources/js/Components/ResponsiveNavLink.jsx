import { Link } from '@inertiajs/react';

export default function ResponsiveNavLink({
    active = false,
    className = '',
    children,
    ...props
}) {
    return (
        <Link
            {...props}
            className={`flex w-full items-start min-h-touch transition duration-150 ease-in-out focus:outline-none ${
                active
                    ? 'clay-responsive-nav-link-active'
                    : 'clay-responsive-nav-link'
            } ${className}`}
        >
            {children}
        </Link>
    );
}

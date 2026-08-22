import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import ThemeToggle from '@/Components/ThemeToggle';
import PlanningNav from '@/Components/PlanningNav';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function AuthenticatedLayout({ header, children }) {
    const { auth, wedding } = usePage().props;
    const user = auth.user;

    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);

    const closeNav = () => setShowingNavigationDropdown(false);

    return (
        <div className="clay-bg min-h-screen">
            <nav className="clay-nav sticky top-0 z-30">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex h-16 justify-between">
                        <div className="flex">
                            <div className="flex shrink-0 items-center">
                                <Link href="/">
                                    <ApplicationLogo className="block h-9 w-auto fill-current text-primary" />
                                </Link>
                            </div>

                            <div className="hidden space-x-2 xl:-my-px xl:ms-10 xl:flex xl:items-center">
                                <NavLink
                                    href={route('dashboard')}
                                    active={route().current('dashboard')}
                                >
                                    Panel
                                </NavLink>
                                {wedding && (
                                    <>
                                        <NavLink
                                            href={route('weddings.categories.index', wedding.id)}
                                            active={route().current('weddings.categories.*')}
                                        >
                                            Categorías
                                        </NavLink>
                                        <NavLink
                                            href={route('weddings.expenses.index', wedding.id)}
                                            active={route().current('weddings.expenses.*')}
                                        >
                                            Gastos
                                        </NavLink>
                                        <NavLink
                                            href={route('weddings.vendors.index', wedding.id)}
                                            active={route().current('weddings.vendors.*')}
                                        >
                                            Proveedores
                                        </NavLink>
                                        <NavLink
                                            href={route('weddings.tables.index', wedding.id)}
                                            active={route().current('weddings.tables.*')}
                                        >
                                            Mesas
                                        </NavLink>
                                        <NavLink
                                            href={route('weddings.guests.index', wedding.id)}
                                            active={route().current('weddings.guests.*')}
                                        >
                                            Invitados
                                        </NavLink>
                                        <PlanningNav
                                            wedding={wedding}
                                            variant="desktop"
                                        />
                                    </>
                                )}
                            </div>
                        </div>

                        <div className="hidden xl:flex xl:items-center xl:gap-3">
                            <ThemeToggle />

                            <div className="relative ms-1">
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                className="clay-btn clay-btn-secondary inline-flex items-center rounded-xl px-4 py-2 text-sm font-medium leading-4 text-gray-600 transition duration-150 ease-in-out focus:outline-none"
                                            >
                                                {user.name}

                                                <svg
                                                    className="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fillRule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clipRule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </Dropdown.Trigger>

                                    <Dropdown.Content>
                                        <Dropdown.Link
                                            href={route('profile.edit')}
                                        >
                                            Perfil
                                        </Dropdown.Link>
                                        <Dropdown.Link
                                            href={route('logout')}
                                            method="post"
                                            as="button"
                                        >
                                            Cerrar sesión
                                        </Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                        </div>

                        <div className="-me-2 flex items-center gap-2 xl:hidden">
                            <ThemeToggle />
                            <button
                                onClick={() =>
                                    setShowingNavigationDropdown(
                                        (previousState) => !previousState,
                                    )
                                }
                                className="clay-btn inline-flex items-center justify-center rounded-xl p-2.5 text-gray-500 transition duration-150 ease-in-out focus:outline-none min-h-touch min-w-[44px]"
                            >
                                <svg
                                    className="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        className={
                                            !showingNavigationDropdown
                                                ? 'inline-flex'
                                                : 'hidden'
                                        }
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        className={
                                            showingNavigationDropdown
                                                ? 'inline-flex'
                                                : 'hidden'
                                        }
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    className={
                        (showingNavigationDropdown ? 'block' : 'hidden') +
                        ' xl:hidden'
                    }
                >
                    <div className="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            href={route('dashboard')}
                            active={route().current('dashboard')}
                            onClick={closeNav}
                        >
                            Panel
                        </ResponsiveNavLink>
                        {wedding && (
                            <>
                                <ResponsiveNavLink
                                    href={route('weddings.categories.index', wedding.id)}
                                    active={route().current('weddings.categories.*')}
                                    onClick={closeNav}
                                >
                                    Categorías
                                </ResponsiveNavLink>
                                <ResponsiveNavLink
                                    href={route('weddings.expenses.index', wedding.id)}
                                    active={route().current('weddings.expenses.*')}
                                    onClick={closeNav}
                                >
                                    Gastos
                                </ResponsiveNavLink>
                                <ResponsiveNavLink
                                    href={route('weddings.vendors.index', wedding.id)}
                                    active={route().current('weddings.vendors.*')}
                                    onClick={closeNav}
                                >
                                    Proveedores
                                </ResponsiveNavLink>
                                <ResponsiveNavLink
                                    href={route('weddings.tables.index', wedding.id)}
                                    active={route().current('weddings.tables.*')}
                                    onClick={closeNav}
                                >
                                    Mesas
                                </ResponsiveNavLink>
                                <ResponsiveNavLink
                                    href={route('weddings.guests.index', wedding.id)}
                                    active={route().current('weddings.guests.*')}
                                    onClick={closeNav}
                                >
                                    Invitados
                                </ResponsiveNavLink>
                                <PlanningNav
                                    wedding={wedding}
                                    variant="mobile"
                                    onNavigate={closeNav}
                                />
                            </>
                        )}
                    </div>

                    <div className="border-t border-white/40 pb-1 pt-4">
                        <div className="px-4">
                            <div className="text-base font-semibold text-gray-800">
                                {user.name}
                            </div>
                            <div className="text-sm font-medium text-gray-500">
                                {user.email}
                            </div>
                        </div>

                        <div className="mt-3 space-y-1">
                            <ResponsiveNavLink href={route('profile.edit')} onClick={closeNav}>
                                Perfil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                method="post"
                                href={route('logout')}
                                as="button"
                            >
                                Cerrar sesión
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            {header && (
                <header className="clay-header">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}

            <main>{children}</main>
        </div>
    );
}

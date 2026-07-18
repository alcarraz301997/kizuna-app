import { Head, Link } from '@inertiajs/react';

export default function Welcome({ auth, laravelVersion, phpVersion }) {
    const features = [
        {
            title: 'Gestión de Gastos',
            description: 'Registra y controla todos los gastos de tu negocio en un solo lugar.',
            icon: (
                <svg className="w-7 h-7 text-primary" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                </svg>
            ),
            cardClass: 'clay-card-indigo',
            iconClass: 'clay-icon-indigo',
        },
        {
            title: 'Control de Mesas',
            description: 'Administra las mesas y asigna invitados de forma sencilla.',
            icon: (
                <svg className="w-7 h-7 text-accent" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h6M9 10.5h6M9 14.25h6" />
                </svg>
            ),
            cardClass: 'clay-card-rose',
            iconClass: 'clay-icon-rose',
        },
        {
            title: 'Proveedores',
            description: 'Lleva un registro de tus proveedores y sus productos.',
            icon: (
                <svg className="w-7 h-7 text-warning" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H18.75M10.5 18.75h3m-3 0a1.5 1.5 0 0 0 3 0m-3 0v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125" />
                </svg>
            ),
            cardClass: 'clay-card-amber',
            iconClass: 'clay-icon-amber',
        },
        {
            title: 'Categorías',
            description: 'Organiza tus gastos por categorías para un mejor análisis.',
            icon: (
                <svg className="w-7 h-7 text-success" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                    <path strokeLinecap="round" strokeLinejoin="round" d="M6 6h.008v.008H6V6Z" />
                </svg>
            ),
            cardClass: 'clay-card-emerald',
            iconClass: 'clay-icon-emerald',
        },
        {
            title: 'Lista de Invitados',
            description: 'Gestiona tu lista de invitados y confirmaciones.',
            icon: (
                <svg className="w-7 h-7 text-sky-500" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
            ),
            cardClass: 'clay-card-sky',
            iconClass: 'clay-icon-sky',
        },
        {
            title: 'Dashboard',
            description: 'Visualiza resúmenes y estadísticas de tu negocio.',
            icon: (
                <svg className="w-7 h-7 text-violet-500" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
            ),
            cardClass: 'clay-card-violet',
            iconClass: 'clay-icon-violet',
        },
    ];

    return (
        <>
            <Head title="Kizuna - Gestión de Eventos" />

            <div className="clay-bg relative overflow-hidden">
                {/* Decorative background blobs */}
                <div className="clay-blob w-72 sm:w-96 h-72 sm:h-96 -top-20 -left-20" style={{ backgroundColor: `rgb(var(--blob-1))` }} />
                <div className="clay-blob w-60 sm:w-80 h-60 sm:h-80 top-40 right-0" style={{ backgroundColor: `rgb(var(--blob-2))` }} />
                <div className="clay-blob w-56 sm:w-72 h-56 sm:h-72 bottom-20 left-1/4" style={{ backgroundColor: `rgb(var(--blob-3))` }} />
                <div className="clay-blob w-48 sm:w-64 h-48 sm:h-64 bottom-0 right-1/4" style={{ backgroundColor: `rgb(var(--blob-4))` }} />

                {/* Header */}
                <header className="relative z-10 px-4 sm:px-6 py-4 sm:py-6 lg:px-12">
                    <div className="max-w-7xl mx-auto flex items-center justify-between">
                        {/* Logo */}
                        <div className="flex items-center gap-3">
                            <div className="clay-icon clay-icon-indigo p-3">
                                <svg className="w-6 sm:w-8 h-6 sm:h-8 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 0 1-.657.643 48.39 48.39 0 0 1-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 0 1-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 0 0-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 0 1-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 0 0 .657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 0 1-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.401.604-.401.959v0c0 .333.277.599.61.58a48.1 48.1 0 0 0 5.427-.63 48.05 48.05 0 0 0 .582-4.717.532.532 0 0 0-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.959.401v0a.656.656 0 0 0 .658-.663 48.422 48.422 0 0 0-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 0 1-.61-.58v0Z" />
                                </svg>
                            </div>
                            <h1 className="text-xl sm:text-2xl font-extrabold text-gray-700 tracking-tight">
                                Kizuna
                            </h1>
                        </div>

                        {/* Navigation */}
                        <nav className="flex items-center gap-2 sm:gap-3">
                            {auth.user ? (
                                <Link
                                    href={route('dashboard')}
                                    className="clay-btn clay-btn-primary px-4 sm:px-6 py-2.5 sm:py-3 font-semibold text-sm min-h-touch"
                                >
                                    Ir al Panel
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={route('login')}
                                        className="clay-btn px-4 sm:px-6 py-2.5 sm:py-3 font-semibold text-gray-600 text-sm min-h-touch"
                                    >
                                        Iniciar Sesión
                                    </Link>
                                    <Link
                                        href={route('register')}
                                        className="clay-btn clay-btn-primary px-4 sm:px-6 py-2.5 sm:py-3 font-semibold text-sm min-h-touch"
                                    >
                                        Registrarse
                                    </Link>
                                </>
                            )}
                        </nav>
                    </div>
                </header>

                {/* Hero Section */}
                <section className="relative z-10 px-4 sm:px-6 pt-8 sm:pt-12 pb-12 sm:pb-16 lg:px-12">
                    <div className="max-w-4xl mx-auto text-center">
                        <div className="clay-card clay-card-indigo inline-block p-3 sm:p-4 mb-6 sm:mb-8">
                            <span className="text-primary font-bold text-xs sm:text-sm uppercase tracking-wider">
                                ✨ Sistema de Gestión
                            </span>
                        </div>

                        <h2 className="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-gray-800 mb-4 sm:mb-6 leading-tight">
                            Gestiona tus eventos
                            <br />
                            <span className="text-transparent bg-clip-text bg-gradient-to-r from-primary via-purple-500 to-accent">
                                de forma sencilla
                            </span>
                        </h2>

                        <p className="text-base sm:text-xl text-gray-500 max-w-2xl mx-auto mb-8 sm:mb-10 leading-relaxed">
                            Controla gastos, mesas, invitados y proveedores todo desde un solo lugar.
                            Diseñado para hacer tu vida más fácil.
                        </p>

                        <div className="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4">
                            {auth.user ? (
                                <Link
                                    href={route('dashboard')}
                                    className="clay-btn clay-btn-primary px-6 sm:px-8 py-3 sm:py-4 font-bold text-base sm:text-lg min-h-touch"
                                >
                                    Ir al Dashboard →
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={route('register')}
                                        className="clay-btn clay-btn-primary px-6 sm:px-8 py-3 sm:py-4 font-bold text-base sm:text-lg min-h-touch"
                                    >
                                        Comenzar Gratis
                                    </Link>
                                    <Link
                                        href={route('login')}
                                        className="clay-btn px-6 sm:px-8 py-3 sm:py-4 font-bold text-gray-600 text-base sm:text-lg min-h-touch"
                                    >
                                        Ya tengo cuenta
                                    </Link>
                                </>
                            )}
                        </div>
                    </div>
                </section>

                {/* Features Section */}
                <section className="relative z-10 px-4 sm:px-6 pb-16 sm:pb-20 lg:px-12">
                    <div className="max-w-6xl mx-auto">
                        <div className="text-center mb-8 sm:mb-12">
                            <h3 className="text-2xl sm:text-3xl font-bold text-gray-700 mb-2 sm:mb-3">
                                Todo lo que necesitas
                            </h3>
                            <p className="text-gray-500 text-base sm:text-lg">
                                Herramientas diseñadas para la gestión integral de tu evento
                            </p>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                            {features.map((feature, index) => (
                                <div
                                    key={index}
                                    className={`clay-card ${feature.cardClass} p-5 sm:p-6 cursor-default`}
                                >
                                    <div className={`clay-icon ${feature.iconClass} p-3 sm:p-4 inline-block mb-3 sm:mb-4`}>
                                        {feature.icon}
                                    </div>
                                    <h4 className="text-lg sm:text-xl font-bold text-gray-700 mb-1 sm:mb-2">
                                        {feature.title}
                                    </h4>
                                    <p className="text-gray-500 text-sm sm:text-base leading-relaxed">
                                        {feature.description}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Footer */}
                <footer className="relative z-10 px-4 sm:px-6 py-6 sm:py-8 lg:px-12">
                    <div className="max-w-7xl mx-auto">
                        <div className="clay-card p-4 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4">
                            <div className="flex items-center gap-2 text-gray-500 text-sm sm:text-base">
                                <span className="font-semibold">Kizuna</span>
                                <span className="text-sm">
                                    — Laravel v{laravelVersion} • PHP v{phpVersion}
                                </span>
                            </div>
                            <div className="text-sm text-gray-400">
                                Hecho con 💜 para gestionar mejor
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}

import { NavLink, useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

const NAV_ITEMS = [
    { to: '/dashboard', label: 'Dashboard', icon: '📊' },
    { to: '/tracking', label: 'Tracking', icon: '🔍' },
];

/** Peta role → link ke halaman kerja Blade (fase berikutnya dipindah ke React). */
const ROLE_LEGACY_LINKS = {
    admin: [
        { href: '/admin', label: 'Admin DB Tiket' },
        { href: '/admin/users', label: 'Kelola User' },
        { href: '/reports', label: 'Laporan' },
    ],
    pemimpin: [{ href: '/pemimpin', label: 'Monitoring' }],
    loket: [{ href: '/loket', label: 'Loket Penerimaan' }],
    verifikator: [{ href: '/verifikator', label: 'Verifikator' }],
    warkah: [{ href: '/warkah', label: 'Warkah' }],
    validator_btel: [{ href: '/validator-bt', label: 'Validator BT' }],
    validator_suel: [{ href: '/validator-su', label: 'Validator SU' }],
    alih_media_btel: [{ href: '/alih-media-bt', label: 'Alih Media BT' }],
    alih_media_suel: [{ href: '/alih-media-su', label: 'Alih Media SU' }],
};

export default function AppLayout({ children }) {
    const { user, logout } = useAuth();
    const navigate = useNavigate();

    const handleLogout = async () => {
        await logout();
        navigate('/login', { replace: true });
    };

    const roleLinks = ROLE_LEGACY_LINKS[user?.role] ?? [];
    const activeClass = 'bg-gold text-white font-semibold shadow-sm';

    return (
        <div className="flex min-h-screen bg-bpn-bg">
            {/* Sidebar */}
            <aside className="fixed inset-y-0 left-0 z-40 hidden w-[260px] flex-col bg-gradient-to-b from-navy to-navy-dark text-white shadow-lg lg:flex">
                {/* Brand */}
                <div className="flex items-center gap-3 border-b border-white/10 px-6 py-5">
                    <img
                        src="/asset/logobpn2026.png"
                        alt="Logo BPN"
                        className="h-11 w-11 rounded-full bg-white object-contain p-0.5"
                    />
                    <div>
                        <h1 className="text-base font-bold tracking-wide text-gold">LOKET 2026</h1>
                        <p className="mt-0.5 text-[11px] leading-snug text-slate-400">
                            Kantah Kota Bandar Lampung
                        </p>
                    </div>
                </div>

                <nav className="flex-1 overflow-y-auto px-3 py-4">
                    {/* Menu Utama */}
                    <p className="mb-2 px-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                        Menu Utama
                    </p>
                    <ul className="space-y-1">
                        {NAV_ITEMS.map((item) => (
                            <li key={item.to}>
                                <NavLink
                                    to={item.to}
                                    className={({ isActive }) =>
                                        `flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition ${isActive ? activeClass : 'text-slate-300 hover:bg-white/10 hover:text-white'}`
                                    }
                                >
                                    <span className="text-base">{item.icon}</span>
                                    {item.label}
                                </NavLink>
                            </li>
                        ))}
                    </ul>

                    {/* Menu Kerja (Blade legacy) — tampil jika ada role */}
                    {roleLinks.length > 0 && (
                        <>
                            <p className="mb-2 mt-6 px-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                                Menu Kerja
                            </p>
                            <ul className="space-y-1">
                                {roleLinks.map((item) => (
                                    <li key={item.href}>
                                        <a
                                            href={item.href}
                                            className="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white"
                                        >
                                            <span className="text-base opacity-60">⚙️</span>
                                            {item.label}
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        </>
                    )}
                </nav>

                {/* Footer sidebar — role badge */}
                <div className="border-t border-white/10 px-4 py-3">
                    <p className="truncate text-xs text-slate-400" title={user?.username}>
                        {user?.name}
                    </p>
                    <p className="text-[11px] text-gold">{user?.role_label}</p>
                </div>
            </aside>

            {/* Main area */}
            <div className="flex flex-1 flex-col pl-0 lg:pl-[260px]">
                {/* Topbar */}
                <header className="sticky top-0 z-30 flex items-center justify-between bg-white px-6 py-3 shadow-sm">
                    <p className="text-sm font-semibold text-navy lg:hidden">LOKET 2026</p>
                    <div className="flex-1" />

                    <div className="flex items-center gap-3">
                        <div className="hidden text-right text-sm md:block">
                            <p className="font-medium text-navy">{user?.name}</p>
                            <p className="text-[11px] text-slate-400">{user?.role_label}</p>
                        </div>
                        <button
                            type="button"
                            onClick={handleLogout}
                            className="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-rose-50 hover:text-rose-700"
                        >
                            Keluar
                        </button>
                    </div>
                </header>

                {/* Content */}
                <main className="container mx-auto flex-1 px-4 py-6 lg:px-6">{children}</main>

                {/* Footer */}
                <footer className="border-t bg-white py-3 text-center text-xs text-slate-400">
                    &copy; {new Date().getFullYear()} Kantor Pertanahan Kota Bandar Lampung &bull; LOKET 2026 v2.2
                </footer>
            </div>
        </div>
    );
}
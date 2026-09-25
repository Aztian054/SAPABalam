import { useState } from 'react';
import { Navigate, useNavigate } from 'react-router-dom';
import Spinner from '../components/Spinner';
import { useAuth } from '../contexts/AuthContext';

export default function Login() {
    const { user, login } = useAuth();
    const navigate = useNavigate();

    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [submitting, setSubmitting] = useState(false);

    if (user) {
        return <Navigate to="/dashboard" replace />;
    }

    const handleSubmit = async (event) => {
        event.preventDefault();
        setError('');
        setSubmitting(true);

        try {
            await login({ username, password });
            navigate('/dashboard', { replace: true });
        } catch (err) {
            setError(err.response?.data?.message ?? 'Gagal masuk. Silakan coba lagi.');
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div className="flex min-h-screen bg-bpn-bg">
            {/* Panel brand kiri */}
            <div className="hidden w-1/2 flex-col justify-between bg-gradient-to-br from-navy via-navy to-navy-dark p-12 text-white lg:flex">
                <div>
                    <div className="flex items-center gap-4">
                        <img
                            src="/asset/logobpn2026.png"
                            alt="Logo BPN"
                            className="h-14 w-14 rounded-full bg-white object-contain p-1 shadow-md"
                        />
                        <div>
                            <p className="text-2xl font-extrabold tracking-wide text-gold">LOKET 2026</p>
                            <p className="mt-1 text-sm text-slate-300">Sistem Loket Pelayanan Pertanahan Elektronik BMN</p>
                        </div>
                    </div>
                </div>

                <div className="space-y-4">
                    <h1 className="text-3xl font-bold leading-snug">
                        Kantor Pertanahan
                        <br />
                        Kota Bandar Lampung
                    </h1>
                    <p className="max-w-md text-sm leading-relaxed text-slate-300">
                        Alur kerja 9 peran: Loket, Verifikator, Warkah, Validator, Alih Media, hingga monitoring
                        Pemimpin dan DB Tiket Admin.
                    </p>
                </div>

                <p className="text-xs text-slate-400">
                    &copy; {new Date().getFullYear()} Kementerian ATR/BPN &bull; v2.2
                </p>
            </div>

            {/* Form kanan */}
            <div className="flex w-full items-center justify-center px-6 py-10 lg:w-1/2">
                <div className="w-full max-w-md">
                    <div className="mb-8 lg:hidden">
                        <p className="text-2xl font-extrabold tracking-wide text-navy">
                            LOKET <span className="text-gold">2026</span>
                        </p>
                    </div>

                    <h2 className="text-xl font-bold text-navy">Masuk ke Sistem</h2>
                    <p className="mt-1 text-sm text-slate-500">Gunakan akun resmi Anda untuk mengakses.</p>

                    {error && (
                        <div className="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            {error}
                        </div>
                    )}

                    <form onSubmit={handleSubmit} className="mt-6 space-y-4">
                        <div>
                            <label htmlFor="username" className="mb-1.5 block text-sm font-medium text-slate-700">
                                Username / Email
                            </label>
                            <input
                                id="username"
                                name="username"
                                type="text"
                                autoComplete="username"
                                required
                                value={username}
                                onChange={(event) => setUsername(event.target.value)}
                                placeholder="Masukkan username atau email"
                                className="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-gold focus:ring-2 focus:ring-gold/30"
                            />
                        </div>

                        <div>
                            <label htmlFor="password" className="mb-1.5 block text-sm font-medium text-slate-700">
                                Password
                            </label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                autoComplete="current-password"
                                required
                                value={password}
                                onChange={(event) => setPassword(event.target.value)}
                                placeholder="Masukkan password"
                                className="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-gold focus:ring-2 focus:ring-gold/30"
                            />
                        </div>

                        <button
                            type="submit"
                            disabled={submitting}
                            className="flex w-full items-center justify-center gap-2 rounded-lg bg-navy py-2.5 text-sm font-semibold text-white transition hover:bg-navy-light disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {submitting ? <Spinner className="h-4 w-4" label="" /> : null}
                            {submitting ? 'Mengautentikasi...' : 'Masuk'}
                        </button>
                    </form>

                    <p className="mt-6 text-center text-xs text-slate-400">
                        Lupa password? Hubungi Admin Kantor Anda.
                    </p>
                </div>
            </div>
        </div>
    );
}
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';

export default function TrackingIndex() {
    const navigate = useNavigate();
    const [kode, setKode] = useState('');
    const [error, setError] = useState('');

    const handleSubmit = (event) => {
        event.preventDefault();

        const kodeBersih = kode.trim();
        if (!kodeBersih) {
            setError('Masukkan kode tiket terlebih dahulu.');
            return;
        }

        setError('');
        navigate(`/tracking/${encodeURIComponent(kodeBersih)}`);
    };

    return (
        <div className="flex min-h-screen flex-col bg-bpn-bg">
            {/* Header */}
            <header className="bg-gradient-to-br from-navy to-navy-dark px-6 py-10 text-white shadow-md">
                <div className="mx-auto w-full max-w-2xl">
                    <a
                        href="/"
                        className="inline-flex items-center gap-2 text-sm font-bold tracking-wide text-gold"
                    >
                        <img
                            src="/asset/logobpn2026.png"
                            alt="Logo BPN"
                            className="h-8 w-8 rounded-full bg-white object-contain p-0.5"
                        />
                        LOKET 2026
                    </a>
                    <h1 className="mt-2 text-2xl font-bold">Lacak Status Berkas Anda</h1>
                    <p className="mt-1 text-sm text-slate-300">
                        Sistem Pelayanan Pertanahan Elektronik BMN — Kantor Pertanahan Kota Bandar Lampung
                    </p>

                    <form onSubmit={handleSubmit} className="mt-6">
                        <div className="flex flex-col gap-2 sm:flex-row">
                            <input
                                type="text"
                                value={kode}
                                onChange={(event) => setKode(event.target.value)}
                                placeholder="Contoh: LK/XXXX/2026 atau kode tiket"
                                className="flex-1 rounded-lg border border-white/20 bg-white/10 px-4 py-3 text-sm text-white placeholder-slate-400 outline-none backdrop-blur transition focus:border-gold focus:ring-2 focus:ring-gold/40"
                            />
                            <button
                                type="submit"
                                className="rounded-lg bg-gold px-6 py-3 text-sm font-semibold text-white shadow transition hover:bg-gold-hover"
                            >
                                Lacak
                            </button>
                        </div>
                        {error && <p className="mt-2 text-sm text-rose-300">{error}</p>}
                    </form>
                </div>
            </header>

            {/* Info */}
            <main className="mx-auto w-full max-w-2xl flex-1 px-6 py-10">
                <div className="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <h2 className="text-base font-semibold text-navy">Cara menggunakan</h2>
                    <ol className="mt-3 list-decimal space-y-2 pl-5 text-sm text-slate-600">
                        <li>Masukkan kode tiket pada kotak di atas.</li>
                        <li>Kode dapat Anda peroleh dari bukti pendaftaran di Loket Penerimaan.</li>
                        <li>Anda akan melihat status terkini, timeline proses, dan detail berkas.</li>
                    </ol>
                </div>

                <p className="mt-8 text-center text-xs text-slate-400">
                    &copy; {new Date().getFullYear()} Kantor Pertanahan Kota Bandar Lampung &bull; LOKET 2026
                </p>
            </main>
        </div>
    );
}
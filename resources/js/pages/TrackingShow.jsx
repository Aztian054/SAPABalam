import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import Spinner from '../components/Spinner';
import StatusBadge from '../components/StatusBadge';
import api from '../services/api';

function Timeline({ riwayat }) {
    const items = [...riwayat].reverse();

    if (items.length === 0) {
        return <p className="text-sm text-slate-500">Belum ada aktivitas tercatat untuk tiket ini.</p>;
    }

    return (
        <ol className="relative space-y-6 border-l-2 border-slate-200 pl-6">
            {items.map((item, index) => (
                <li key={`${item.stage_dari}-${item.stage_ke}-${index}`} className="relative">
                    <span className="absolute -left-[31px] top-1 flex h-4 w-4 items-center justify-center rounded-full border-2 border-gold bg-white" />
                    <p className="text-sm font-semibold text-navy">
                        {item.stage_dari} → {item.stage_ke}
                    </p>
                    <p className="mt-0.5 text-xs text-slate-400">{item.waktu}</p>
                    {item.keterangan && <p className="mt-1 text-sm text-slate-600">{item.keterangan}</p>}
                </li>
            ))}
        </ol>
    );
}

export default function TrackingShow() {
    const { kode } = useParams();
    const [tiket, setTiket] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        let cancelled = false;

        async function fetchTiket() {
            setLoading(true);
            setError('');

            try {
                const { data } = await api.get(`/tracking/${encodeURIComponent(kode)}`);
                if (!cancelled) {
                    setTiket(data.data);
                }
            } catch (err) {
                if (!cancelled) {
                    setError(err.response?.data?.message ?? 'Gagal memuat data tracking.');
                }
            } finally {
                if (!cancelled) {
                    setLoading(false);
                }
            }
        }

        fetchTiket();

        return () => {
            cancelled = true;
        };
    }, [kode]);

    return (
        <div className="min-h-screen bg-bpn-bg">
            {/* Header */}
            <header className="bg-gradient-to-br from-navy to-navy-dark px-6 py-8 text-white shadow-md">
                <div className="mx-auto w-full max-w-4xl">
                    <div className="flex items-center justify-between">
                        <Link
                            to="/tracking"
                            className="inline-flex items-center gap-2 text-sm font-bold tracking-wide text-gold"
                        >
                            <img
                                src="/asset/logobpn2026.png"
                                alt="Logo BPN"
                                className="h-8 w-8 rounded-full bg-white object-contain p-0.5"
                            />
                            LOKET 2026
                        </Link>
                        <Link to="/login" className="text-xs text-slate-300 hover:text-white">
                            Masuk Petugas
                        </Link>
                    </div>
                    <h1 className="mt-4 text-2xl font-bold">Status Berkas #{kode}</h1>
                </div>
            </header>

            <main className="mx-auto w-full max-w-4xl px-6 py-8">
                {loading && <Spinner label="Mengambil data tracking..." />}

                {error && (
                    <div className="rounded-xl border border-amber-200 bg-amber-50 p-6 text-center">
                        <p className="text-4xl">🔍</p>
                        <p className="mt-2 text-sm font-medium text-amber-800">{error}</p>
                        <Link
                            to="/tracking"
                            className="mt-4 inline-block rounded-lg bg-navy px-4 py-2 text-sm font-medium text-white transition hover:bg-navy-light"
                        >
                            Coba kode lain
                        </Link>
                    </div>
                )}

                {!loading && !error && tiket && (
                    <div className="space-y-6">
                        {/* Ringkasan status */}
                        <div className="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                            <div className="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                                <div>
                                    <p className="text-xs uppercase tracking-wide text-slate-400">Status Terkini</p>
                                    <div className="mt-1 flex flex-wrap items-center gap-2">
                                        <h2 className="text-xl font-bold text-navy">{tiket.status_label}</h2>
                                        <StatusBadge badge={tiket.status_badge} label={tiket.status_label} />
                                    </div>
                                    <p className="mt-1 text-sm text-slate-500">
                                        {tiket.nama_pemohon}
                                    </p>
                                </div>
                                <div className="text-sm text-slate-500">
                                    <p>
                                        Masuk: <span className="font-medium text-navy">{tiket.tanggal_masuk}</span>
                                    </p>
                                    {tiket.tanggal_selesai && (
                                        <p>
                                            Selesai:{' '}
                                            <span className="font-medium text-emerald-600">{tiket.tanggal_selesai}</span>
                                        </p>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Timeline */}
                        <div className="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                            <h3 className="mb-5 text-base font-semibold text-navy">Riwayat Proses</h3>
                            <Timeline riwayat={tiket.riwayat_timeline} />
                        </div>

                        {/* Detail berkas */}
                        <div className="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                            <h3 className="mb-4 text-base font-semibold text-navy">Detail Permohonan</h3>
                            <dl className="grid grid-cols-1 gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                                <div>
                                    <dt className="text-xs text-slate-400">Jenis Permohonan</dt>
                                    <dd className="font-medium text-slate-700">
                                        {tiket.jenis_permohonan?.nama ?? '—'}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-xs text-slate-400">Jumlah Bidang</dt>
                                    <dd className="font-medium text-slate-700">{tiket.jumlah_bidang ?? 0}</dd>
                                </div>
                                <div>
                                    <dt className="text-xs text-slate-400">No. Tiket</dt>
                                    <dd className="font-mono font-medium text-slate-700">{tiket.no_tiket}</dd>
                                </div>
                            </dl>

                            {tiket.bidang_tanah?.length > 0 && (
                                <>
                                    <h4 className="mb-2 mt-6 text-sm font-semibold text-navy">Bidang Tanah</h4>
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-left text-sm">
                                            <thead>
                                                <tr className="border-b border-slate-200 text-xs text-slate-400">
                                                    <th className="py-2 pr-3">NIB</th>
                                                    <th className="py-2 pr-3">No. Sertifikat Elektronik</th>
                                                    <th className="py-2 pr-3">Jenis Hak</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {tiket.bidang_tanah.map((b, index) => (
                                                    <tr key={`${b.nib}-${index}`} className="border-b border-slate-100">
                                                        <td className="py-2 pr-3 font-mono text-xs text-slate-600">
                                                            {b.nib ?? '—'}
                                                        </td>
                                                        <td className="py-2 pr-3 text-slate-600">
                                                            {b.no_sertifikat_elektronik ?? '—'}
                                                        </td>
                                                        <td className="py-2 pr-3 text-slate-600">{b.jenis_hak ?? '—'}</td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </>
                            )}
                        </div>
                    </div>
                )}
            </main>
        </div>
    );
}
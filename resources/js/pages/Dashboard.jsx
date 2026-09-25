import { useEffect, useState } from 'react';
import { Bar, BarChart, CartesianGrid, Cell, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import Spinner from '../components/Spinner';
import StatCard from '../components/StatCard';
import StatusBadge from '../components/StatusBadge';
import api from '../services/api';

/* ── Ikon SVG tipis ala Lucide ── */
const iconProps = { width: 20, height: 20, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 1.6, strokeLinecap: 'round', strokeLinejoin: 'round' };

const IconFolder = () => (
    <svg {...iconProps}>
        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" />
    </svg>
);

const IconInbox = () => (
    <svg {...iconProps}>
        <path d="M22 12H2" />
        <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z" />
        <path d="M6 16h.01" />
        <path d="M10 16h.01" />
    </svg>
);

const IconCheckCircle = () => (
    <svg {...iconProps}>
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
        <path d="m9 11 3 3L22 4" />
    </svg>
);

const IconRotateCcw = () => (
    <svg {...iconProps}>
        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
        <path d="M3 3v5h5" />
    </svg>
);

const STAGE_LABELS = {
    diterima: 'Diterima',
    verifikasi: 'Verifikasi',
    warkah: 'Warkah',
    validasi_btel: 'Validasi BT',
    validasi_suel: 'Validasi SU',
    alih_media_btel: 'Alih Media BT',
    alih_media_suel: 'Alih Media SU',
    selesai: 'Selesai',
    dikembalikan: 'Dikembalikan',
    batal: 'Batal',
};

const STAGE_COLORS = {
    diterima: '#38bdf8',
    verifikasi: '#8b5cf6',
    warkah: '#14b8a6',
    validasi_btel: '#06b6d4',
    validasi_suel: '#f59e0b',
    alih_media_btel: '#10b981',
    alih_media_suel: '#2dd4bf',
    selesai: '#0b2239',
    dikembalikan: '#f472b6',
    batal: '#cbd5e1',
};

/* Palet pastel/muted untuk bar bulanan (selaras brand) */
const MONTHLY_PALETTE = ['#0b2239', '#14b8a6', '#38bdf8', '#f2c94c', '#34d399', '#a78bfa'];

export default function Dashboard() {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        let cancelled = false;

        async function fetchDashboard() {
            setLoading(true);
            setError('');

            try {
                const { data: responseData } = await api.get('/dashboard');
                if (!cancelled) {
                    setData(responseData.data);
                }
            } catch (err) {
                if (!cancelled) {
                    setError(err.response?.data?.message ?? 'Gagal memuat data dashboard.');
                }
            } finally {
                if (!cancelled) {
                    setLoading(false);
                }
            }
        }

        fetchDashboard();

        return () => {
            cancelled = true;
        };
    }, []);

    if (loading) {
        return <Spinner label="Memuat dashboard..." />;
    }

    if (error) {
        return (
            <div className="rounded-xl border border-rose-200 bg-rose-50 p-6 text-center">
                <p className="text-4xl">⚠️</p>
                <p className="mt-2 text-sm font-medium text-rose-700">{error}</p>
            </div>
        );
    }

    if (!data) {
        return null;
    }

    const stageSeries = Object.entries(data.stage_counts ?? {}).map(([key, value]) => ({
        key,
        name: STAGE_LABELS[key] ?? key,
        count: value,
        color: STAGE_COLORS[key] ?? '#94a3b8',
    }));

    const totalStage = stageSeries.reduce((sum, item) => sum + item.count, 0) || 1;

    return (
        <div className="space-y-6">
            {/* Stat cards */}
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard label="Total Tiket" value={data.total_tiket} icon={<IconFolder />} accent="bg-slate-100 text-slate-600" />
                <StatCard label="Masuk Hari Ini" value={data.tiket_hari_ini} icon={<IconInbox />} accent="bg-sky-100 text-sky-600" />
                <StatCard label="Tiket Selesai" value={data.tiket_selesai} icon={<IconCheckCircle />} accent="bg-emerald-100 text-emerald-600" />
                <StatCard label="Dikembalikan" value={data.tiket_dikembalikan} icon={<IconRotateCcw />} accent="bg-rose-100 text-rose-500" />
            </div>

            {/* Chart + distribusi tahap */}
            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {/* Grafik bulanan */}
                <div className="animate-fade-up rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm ring-1 ring-slate-100 backdrop-blur-sm lg:col-span-2">
                    <h3 className="mb-4 text-base font-semibold text-navy">Jumlah Tiket per Bulan (6 bulan)</h3>
                    <div className="h-64">
                        <ResponsiveContainer width="100%" height="100%">
                            <BarChart data={data.monthly_data ?? []} margin={{ top: 5, right: 10, left: -20, bottom: 0 }}>
                                <CartesianGrid strokeDasharray="3 3" stroke="#e2e8f0" />
                                <XAxis dataKey="label" tick={{ fontSize: 12, fill: '#64748b' }} />
                                <YAxis allowDecimals={false} tick={{ fontSize: 12, fill: '#64748b' }} />
                                <Tooltip cursor={{ fill: 'rgba(148, 163, 184, 0.1)' }} />
                                <Bar
                                    dataKey="count"
                                    name="Jumlah Tiket"
                                    radius={[8, 8, 0, 0]}
                                    animationDuration={1200}
                                    animationEasing="ease-out"
                                >
                                    {(data.monthly_data ?? []).map((entry, index) => (
                                        <Cell key={`cell-${index}`} fill={MONTHLY_PALETTE[index % MONTHLY_PALETTE.length]} />
                                    ))}
                                </Bar>
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                </div>

                {/* Distribusi tahap */}
                <div className="animate-fade-up rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm ring-1 ring-slate-100 backdrop-blur-sm" style={{ animationDelay: '0.06s' }}>
                    <h3 className="mb-4 text-base font-semibold text-navy">Distribusi Per Tahap</h3>
                    <div className="space-y-3">
                        {stageSeries.map((item) => (
                            <div key={item.key}>
                                <div className="mb-1 flex items-center justify-between text-sm">
                                    <span className="font-medium text-slate-600">{item.name}</span>
                                    <span className="text-xs font-semibold text-navy">{item.count}</span>
                                </div>
                                <div className="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                    <div
                                        className="h-full rounded-full transition-all duration-700 ease-out"
                                        style={{
                                            width: `${Math.max((item.count / totalStage) * 100, item.count > 0 ? 4 : 0)}%`,
                                            backgroundColor: item.color,
                                        }}
                                    />
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* Aktivitas + Tiket terbaru */}
            <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                {/* Aktivitas terkini */}
                <div className="animate-fade-up rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm ring-1 ring-slate-100 backdrop-blur-sm" style={{ animationDelay: '0.12s' }}>
                    <h3 className="mb-4 text-base font-semibold text-navy">Aktivitas Terkini</h3>
                    {data.recent_activities?.length === 0 && (
                        <p className="text-sm text-slate-500">Belum ada aktivitas tercatat.</p>
                    )}
                    <ul className="relative ml-1.5 space-y-5 border-l-2 border-slate-100 pl-6">
                        {data.recent_activities?.map((activity) => (
                            <li key={activity.id} className="relative">
                                <span className="absolute -left-[31px] top-1.5 h-3 w-3 rounded-full border-2 border-gold bg-white shadow-[0_0_0_3px_rgba(198,146,20,0.12)]" />
                                <p className="text-sm font-medium text-slate-700">{activity.keterangan}</p>
                                <p className="mt-0.5 text-xs text-slate-400">
                                    <span className="font-semibold text-navy">{activity.tiket_kode ?? '—'}</span>
                                    {activity.user_name ? ` • ${activity.user_name}` : ''}
                                    {activity.waktu ? ` • ${activity.waktu}` : ''}
                                </p>
                            </li>
                        ))}
                    </ul>
                </div>

                {/* Tiket terbaru */}
                <div className="animate-fade-up rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm ring-1 ring-slate-100 backdrop-blur-sm" style={{ animationDelay: '0.18s' }}>
                    <h3 className="mb-4 text-base font-semibold text-navy">Tiket Terbaru</h3>
                    {data.recent_tikets?.length === 0 && (
                        <p className="text-sm text-slate-500">Belum ada tiket pada lingkup Anda.</p>
                    )}
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead>
                                <tr className="border-b border-slate-200 text-[0.7rem] uppercase tracking-wide text-slate-400">
                                    <th className="py-2.5 pr-3">Kode</th>
                                    <th className="py-2.5 pr-3">Pemohon</th>
                                    <th className="py-2.5 pr-3">Jenis</th>
                                    <th className="py-2.5 pr-3">Masuk</th>
                                    <th className="py-2.5">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {data.recent_tikets?.map((tiket) => (
                                    <tr key={tiket.id} className="border-b border-slate-100 transition-colors hover:bg-slate-50/70">
                                        <td className="py-3.5 pr-3 font-mono text-xs font-semibold text-navy">{tiket.kode_tiket}</td>
                                        <td className="py-3.5 pr-3 text-slate-600">
                                            <span className="block max-w-[140px] truncate">{tiket.nama_pemohon}</span>
                                        </td>
                                        <td className="py-3.5 pr-3 text-slate-600">{tiket.jenis_permohonan ?? '—'}</td>
                                        <td className="py-3.5 pr-3 text-slate-600">{tiket.tanggal_masuk}</td>
                                        <td className="py-3.5">
                                            <StatusBadge badge={tiket.status_badge} label={tiket.status_label} />
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    );
}
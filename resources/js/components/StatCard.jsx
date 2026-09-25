export default function StatCard({ label, value, icon, accent = 'bg-navy text-white' }) {
    return (
        <div className="rounded-2xl border border-white/60 bg-white/80 p-5 shadow-sm ring-1 ring-slate-100 backdrop-blur-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg animate-fade-up">
            <div className="flex items-start justify-between">
                <div className="min-w-0">
                    <p className="text-xs font-medium uppercase tracking-wide text-slate-400">{label}</p>
                    <p className="mt-1.5 text-2xl font-bold text-navy">{value}</p>
                </div>
                {icon ? (
                    <div className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ${accent}`}>
                        {icon}
                    </div>
                ) : null}
            </div>
        </div>
    );
}
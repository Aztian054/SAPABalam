const BADGE_STYLES = {
    warning: 'bg-amber-100 text-amber-800 border-amber-200',
    info: 'bg-sky-100 text-sky-800 border-sky-200',
    secondary: 'bg-slate-200 text-slate-700 border-slate-300',
    primary: 'bg-blue-100 text-blue-800 border-blue-200',
    purple: 'bg-purple-100 text-purple-800 border-purple-200',
    dark: 'bg-slate-800 text-white border-slate-800',
    indigo: 'bg-indigo-100 text-indigo-800 border-indigo-200',
    success: 'bg-emerald-100 text-emerald-800 border-emerald-200',
    danger: 'bg-rose-100 text-rose-800 border-rose-200',
    light: 'bg-slate-100 text-slate-600 border-slate-200',
};

export default function StatusBadge({ badge, label, className = '' }) {
    const style = BADGE_STYLES[badge] ?? BADGE_STYLES.light;

    return (
        <span
            className={`inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium ${style} ${className}`}
        >
            {label}
        </span>
    );
}
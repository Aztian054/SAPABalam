export default function Spinner({ className = 'h-8 w-8', label = 'Memuat...' }) {
    return (
        <div className="flex flex-col items-center justify-center gap-3 py-12 text-slate-500">
            <div
                className={`animate-spin rounded-full border-4 border-navy/20 border-t-gold ${className}`}
                role="status"
                aria-label={label}
            />
            <span className="text-sm">{label}</span>
        </div>
    );
}
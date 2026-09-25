import { BrowserRouter, Navigate, Route, Routes, useLocation, useNavigate } from 'react-router-dom';
import Spinner from './components/Spinner';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import AppLayout from './layouts/AppLayout';
import Dashboard from './pages/Dashboard';
import Login from './pages/Login';
import TrackingIndex from './pages/TrackingIndex';
import TrackingShow from './pages/TrackingShow';

function FullScreenSpinner() {
    return (
        <div className="flex min-h-screen items-center justify-center bg-bpn-bg">
            <Spinner label="Memeriksa sesi login..." />
        </div>
    );
}

function RequireAuth({ children }) {
    const { user, loading } = useAuth();
    const location = useLocation();

    if (loading) {
        return <FullScreenSpinner />;
    }

    if (!user) {
        return <Navigate to="/login" replace state={{ from: location.pathname }} />;
    }

    return children;
}

function NotFound() {
    const navigate = useNavigate();

    return (
        <div className="flex min-h-screen flex-col items-center justify-center bg-bpn-bg px-4 text-center">
            <p className="text-6xl font-extrabold text-navy">404</p>
            <h1 className="mt-2 text-xl font-semibold text-slate-800">Halaman tidak ditemukan</h1>
            <p className="mt-2 max-w-md text-sm text-slate-500">
                Halaman yang Anda cari belum tersedia di aplikasi baru, atau URL tidak benar.
            </p>
            <button
                type="button"
                onClick={() => navigate('/dashboard')}
                className="mt-6 rounded-lg bg-navy px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-navy-light"
            >
                Kembali ke Dashboard
            </button>
        </div>
    );
}

function AppRoutes() {
    return (
        <Routes>
            <Route path="/" element={<Navigate to="/dashboard" replace />} />
            <Route path="/login" element={<Login />} />

            {/* Tracking publik — tanpa login */}
            <Route path="/tracking" element={<TrackingIndex />} />
            <Route path="/tracking/:kode*" element={<TrackingShow />} />

            {/* Halaman terproteksi */}
            <Route
                path="/dashboard"
                element={
                    <RequireAuth>
                        <AppLayout>
                            <Dashboard />
                        </AppLayout>
                    </RequireAuth>
                }
            />

            <Route path="*" element={<NotFound />} />
        </Routes>
    );
}

export default function AppRoot() {
    return (
        <BrowserRouter basename="/app">
            <AuthProvider>
                <AppRoutes />
            </AuthProvider>
        </BrowserRouter>
    );
}
import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import api, { fetchCsrfCookie } from '../services/api';

const AuthContext = createContext(null);

/**
 * AuthProvider — menyimpan sesi user SPA.
 *
 * - `loading`: true saat pemeriksaan sesi awal (`GET /api/v1/me`).
 * - `login()`: ambil cookie CSRF lalu POST /api/v1/login.
 * - `logout()`: POST /api/v1/logout lalu reset state lokal.
 */
export function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);

    const loadMe = useCallback(async () => {
        try {
            const { data } = await api.get('/me');
            setUser(data.data?.user ?? null);
        } catch {
            setUser(null);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        loadMe();
    }, [loadMe]);

    const login = useCallback(async ({ username, password }) => {
        await fetchCsrfCookie();
        const { data } = await api.post('/login', { username, password });
        setUser(data.data.user);
        return data;
    }, []);

    const logout = useCallback(async () => {
        try {
            await api.post('/logout');
        } catch {
            // Tetap reset state lokal walau panggilan keluar gagal.
        } finally {
            setUser(null);
        }
    }, []);

    const value = useMemo(() => ({ user, loading, login, logout }), [user, loading, login, logout]);

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
    const ctx = useContext(AuthContext);
    if (!ctx) {
        throw new Error('useAuth harus dipakai di dalam <AuthProvider>.');
    }

    return ctx;
}
import axios from 'axios';

/**
 * Klien HTTP untuk API v1 — mode SPA (session cookie Sanctum).
 *
 * Selalu mengirim kredensial cookie (withCredentials) serta header
 * X-XSRF-TOKEN otomatis dari cookie XSRF-TOKEN yang diset Sanctum.
 */
const api = axios.create({
    baseURL: '/api/v1',
    withCredentials: true,
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

/** Sinkronkan cookie CSRF Sanctum sebelum request stateful pertama. */
export async function fetchCsrfCookie() {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
}

export default api;
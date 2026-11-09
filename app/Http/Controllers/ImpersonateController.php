<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ImpersonateMiddleware;
use App\Models\User;
use App\Services\LogAktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * "Lihat/Coba Sebagai Akun" — impersonate read-only untuk Super Admin.
 *
 * Sesi asli tidak ditimpa; lihat ImpersonateMiddleware.
 */
class ImpersonateController extends Controller
{
    public function __construct(
        private readonly LogAktivitasService $log,
    ) {}

    public function index(): View
    {
        return view('super_admin.impersonate', [
            'penggunaPerJabatan' => User::query()
                ->where('role', '!=', 'super_admin')
                ->where('is_active', true)
                ->urutHierarki()
                ->get()
                ->groupBy(fn (User $user): string => $user->role_label),
        ]);
    }

    public function start(Request $request, int $id): RedirectResponse
    {
        $target = User::findOrFail($id);

        // Akun Super Admin lain tidak boleh di-impersonate (diperlakukan tidak ada).
        if ($target->isSuperAdmin() || ! $target->is_active) {
            abort(404);
        }

        $request->session()->put([
            ImpersonateMiddleware::KUNCI_TARGET => $target->id,
            ImpersonateMiddleware::KUNCI_ASAL => Auth::id(),
        ]);
        $request->session()->regenerate();

        $this->log->catat(
            'MULAI IMPERSONASI',
            $target->username,
            'Melihat sebagai '.$target->role_label,
        );

        return redirect()->route('dashboard')
            ->with('success', "Anda melihat sebagai {$target->name} (mode baca-saja).");
    }

    public function stop(Request $request): RedirectResponse
    {
        // Bukan mode lihat/coba: jangan pernah mengeluarkan pengguna aktif.
        if (! $request->session()->has(ImpersonateMiddleware::KUNCI_TARGET)) {
            return redirect()->route('dashboard')
                ->with('warning', 'Anda tidak sedang dalam mode lihat/coba sebagai akun.');
        }

        $asal = User::find($request->session()->get(ImpersonateMiddleware::KUNCI_ASAL));
        $target = User::find($request->session()->get(ImpersonateMiddleware::KUNCI_TARGET));

        $request->session()->forget([
            ImpersonateMiddleware::KUNCI_TARGET,
            ImpersonateMiddleware::KUNCI_ASAL,
        ]);

        if (! $asal?->isSuperAdmin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Sesi Super Admin tidak ditemukan. Silakan masuk kembali.');
        }

        if ($target) {
            $this->log->catat(
                'SELESAI IMPERSONASI',
                $target->username,
                'Kembali ke Super Admin',
                $asal,
            );
        }

        return redirect()->route('dashboard')->with('success', 'Kembali sebagai Super Admin.');
    }
}

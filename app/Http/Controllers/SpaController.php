<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * SpaController — menyajikan shell aplikasi React (SPA).
 *
 * Halaman Blade yang sudah dimigrasikan ke React diarahkan ke sini;
 * React Router menangani seluruh routing sisi klien di bawah prefix /app.
 * Route Blade yang belum dimigrasikan tetap dilayani controller lama.
 */
class SpaController extends Controller
{
    public function __invoke(): View
    {
        return view('spa');
    }
}

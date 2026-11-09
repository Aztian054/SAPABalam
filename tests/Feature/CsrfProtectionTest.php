<?php

namespace Tests\Feature;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Arr;
use Tests\TestCase;

class CsrfProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_handler_419_untuk_request_browser_mengarahkan_kembali_dengan_pesan(): void
    {
        $request = Request::create(route('login.submit'), 'POST', [
            'username' => 'admin',
            'password' => 'admin123',
            '_token' => 'token-basi',
        ]);
        $session = app('session')->driver('array');
        $session->start();
        $request->setLaravelSession($session);

        $response = app(ExceptionHandler::class)->render($request, new TokenMismatchException('CSRF token mismatch.'));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(
            'Sesi Anda telah berakhir atau token keamanan tidak valid. Silakan muat ulang halaman dan coba lagi.',
            $response->getSession()->get('error'),
        );

        // Input tetap dikembalikan, kecuali field sensitif.
        $oldInput = $response->getSession()->get('_old_input', []);
        $this->assertSame('admin', Arr::get($oldInput, 'username'));
        $this->assertArrayNotHasKey('_token', $oldInput);
        $this->assertArrayNotHasKey('password', $oldInput);
    }

    public function test_handler_419_untuk_request_json_mengembalikan_respon_json_419(): void
    {
        $request = Request::create(route('login.submit'), 'POST', []);
        $request->headers->set('Accept', 'application/json');
        $session = app('session')->driver('array');
        $session->start();
        $request->setLaravelSession($session);

        $response = app(ExceptionHandler::class)->render($request, new TokenMismatchException('CSRF token mismatch.'));

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(419, $response->getStatusCode());
        $this->assertSame(
            'Sesi Anda telah berakhir atau token keamanan tidak valid. Silakan muat ulang halaman dan coba lagi.',
            json_decode($response->getContent(), true)['message'],
        );
    }
}

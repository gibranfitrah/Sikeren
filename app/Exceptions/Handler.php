<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Tangani TokenMismatchException (Error 419 Page Expired) secara otomatis dan mulus
        $this->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            try {
                $request->session()->regenerateToken();
            } catch (\Throwable $ignored) {
            }

            if ($request->is('actionlogin') || $request->is('login') || $request->is('logout') || $request->is('actionlogout')) {
                return redirect()->route('login')
                    ->with('info', 'Sesi keamanan login Anda telah diperbarui. Silakan masukkan akun Anda untuk masuk.');
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi Anda telah kedaluwarsa. Silakan refresh halaman dan coba lagi.',
                    'csrf_token' => csrf_token(),
                ], 419);
            }

            return redirect()->back()
                ->withInput($request->except(['password', '_token']))
                ->with('error', 'Sesi halaman telah kedaluwarsa (419). Token keamanan telah diperbarui otomatis, silakan coba lagi.');
        });
    }
}

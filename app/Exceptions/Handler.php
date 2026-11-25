<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        // Tangani TokenMismatchException atau status HTTP 419 (Page Expired) agar tidak pernah memunculkan halaman error 419
        if ($e instanceof TokenMismatchException ||
            ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface && $e->getStatusCode() === 419)) {

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesi telah kedaluwarsa. Silakan refresh halaman.'], 419);
            }

            if ($request->is('logout')) {
                \Illuminate\Support\Facades\Auth::logout();
                return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
            }

            return redirect()->route('login')->with('info', 'Sesi telah diperbarui otomatis. Silakan masuk kembali.');
        }

        return parent::render($request, $e);
    }
}

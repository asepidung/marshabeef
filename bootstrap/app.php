<?php

use App\Http\Middleware\RequirePin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'pin' => RequirePin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Dashboard bisa dibiarkan terbuka berjam-jam; token yang basi jangan berujung halaman 419.
        // Laravel mengubah TokenMismatchException menjadi HttpException 419 sebelum handler ini dipanggil.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            return redirect()->route('login')->with('status', 'Halaman kedaluwarsa. Silakan masukkan PIN lagi.');
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

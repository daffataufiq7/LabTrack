<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesi Anda telah kedaluwarsa. Silakan refresh halaman.'], 419);
            }
            if (auth()->check()) {
                return back()->withInput()->with('error', 'Sesi keamanan formulir Anda telah disegarkan secara otomatis. Silakan ulangi tindakan Anda.');
            }
            return redirect()->route('login')->with('info', 'Sesi masuk Anda telah berakhir. Silakan masuk kembali.');
        });
    })->create();

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'agent.auth' => \App\Http\Middleware\AuthenticateAgent::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
        
        // Usar nuestro middleware CSRF personalizado para excluir rutas de FIRMA PERÚ
        $middleware->web(remove: [
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // El token CSRF expiró (419): en vez de mostrar la página de "Página expirada",
        // devolvemos al usuario al formulario anterior con un mensaje amable y los
        // datos que había ingresado (excepto la contraseña).
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tu sesión expiró por inactividad. Vuelve a intentarlo.',
                ], 419);
            }

            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('status', 'Tu sesión expiró por inactividad. Por favor, inténtalo de nuevo.');
        });
    })->create();

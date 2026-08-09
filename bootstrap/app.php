<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'sip.callback.auth' => \App\Http\Middleware\VerifySipCallbackAuth::class,
        ]);
        // El callback de SIP es un POST servidor-a-servidor sin sesión/cookie de este sitio,
        // así que no puede llevar un token CSRF -- se autentica solo por Basic Auth (ver
        // VerifySipCallbackAuth), igual que exige la especificación de SIP.
        //
        // La versión demo de Actualizar Datos se llama desde otro origen (otro sitio estático,
        // sin cookie de sesión de este dominio) -- su protección es el token cifrado que viaja
        // en el body (ver DemoActualizarDatosController), no CSRF.
        $middleware->validateCsrfTokens(except: [
            'api/pagos/sip/confirmar-pago',
            'api/demo/actualizar-datos/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->is('rcadmin*') || config('app.debug')) {
                return null;
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            if (! in_array($status, [403, 404, 419, 500, 503])) {
                return null;
            }

            return Inertia::render('Error', ['status' => $status])
                ->toResponse($request)
                ->setStatusCode($status);
        });
    })->create();

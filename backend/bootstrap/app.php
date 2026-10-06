<?php

use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenBlacklistedException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // DR-002: middleware de validação de role (rule)
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // DR-003: exceções JWT viram 401 JSON (nunca 500)
        $asUnauthorized = function (\Throwable $e, Request $request): ?\Illuminate\Http\JsonResponse {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(
                    ['message' => $e->getMessage() ?: 'Unauthenticated.'],
                    401,
                );
            }

            return null; // deixa o handler default tratar (rotas web)
        };

        $exceptions->render(fn (TokenExpiredException $e, Request $r) => $asUnauthorized($e, $r));
        $exceptions->render(fn (TokenInvalidException $e, Request $r) => $asUnauthorized($e, $r));
        $exceptions->render(fn (TokenBlacklistedException $e, Request $r) => $asUnauthorized($e, $r));
        $exceptions->render(fn (JWTException $e, Request $r) => $asUnauthorized($e, $r));
    })->create();

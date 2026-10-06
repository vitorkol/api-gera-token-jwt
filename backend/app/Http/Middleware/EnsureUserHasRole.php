<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * DR-002/DR-003 — valida a role (rule) do usuário autenticado via JWT.
 * Uso: Route::middleware('role:admin,manager')
 * Nega com 403 quando autenticado sem nenhuma das roles exigidas.
 */
class EnsureUserHasRole
{
    /**
     * @param  string  ...$roles  Roles permitidas (qualquer uma delas).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth('api')->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            return response()->json([
                'message' => 'Esta ação requer a role: '.implode(', ', $roles).'.',
            ], 403);
        }

        return $next($request);
    }
}

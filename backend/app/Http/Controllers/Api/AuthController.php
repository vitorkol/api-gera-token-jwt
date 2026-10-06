<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * POST /api/register — cria usuário (role fixa: user) e já retorna o JWT.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        // DR-003: auto-cadastro nunca promove role
        $user = User::create($data + ['role' => 'user']);

        $token = auth('api')->login($user);

        return response()->json([
            'user' => $user,
            ...$this->tokenPayload($token),
        ], 201);
    }

    /**
     * POST /api/login — valida credenciais e gera o JWT.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json([
                'message' => 'Email ou senha inválidos.',
            ], 401);
        }

        return response()->json($this->tokenPayload($token));
    }

    /**
     * GET /api/me — valida o token (auth:api) e retorna o usuário autenticado.
     */
    public function me(): JsonResponse
    {
        return response()->json([
            'user' => auth('api')->user(),
        ]);
    }

    /**
     * POST /api/refresh — renova o token.
     */
    public function refresh(): JsonResponse
    {
        return response()->json($this->tokenPayload(auth('api')->refresh()));
    }

    /**
     * POST /api/logout — invalida o token (blacklist).
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'message' => 'Token invalidado com sucesso.',
        ]);
    }

    private function tokenPayload(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ];
    }
}

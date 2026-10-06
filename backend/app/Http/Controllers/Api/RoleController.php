<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    /**
     * GET /api/role — valida o token (auth:api) e devolve a role (rule).
     * Fonte de verdade: banco; a role também viaja no claim do JWT.
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'role' => auth('api')->user()->role,
        ]);
    }

    /**
     * GET /api/admin/ping — demo de validação de rule (middleware role:admin).
     */
    public function adminPing(): JsonResponse
    {
        return response()->json([
            'message' => 'Pong! Você tem a role admin. 🛡️',
        ]);
    }
}

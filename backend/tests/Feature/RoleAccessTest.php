<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_endpoint_valida_token_e_retorna_role(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = auth('api')->login($user);

        $this->getJson('/api/role', ['Authorization' => "Bearer {$token}"])
            ->assertOk()
            ->assertJsonPath('role', 'user');
    }

    public function test_role_endpoint_sem_token_retorna_401(): void
    {
        $this->getJson('/api/role')
            ->assertStatus(401);
    }

    public function test_admin_ping_permitido_para_role_admin(): void
    {
        $token = auth('api')->login(User::factory()->create(['role' => 'admin']));

        $this->getJson('/api/admin/ping', ['Authorization' => "Bearer {$token}"])
            ->assertOk()
            ->assertJsonStructure(['message']);
    }

    public function test_admin_ping_negado_para_role_user_403(): void
    {
        $token = auth('api')->login(User::factory()->create(['role' => 'user']));

        $this->getJson('/api/admin/ping', ['Authorization' => "Bearer {$token}"])
            ->assertStatus(403)
            ->assertJsonPath('message', "Esta ação requer a role: admin.");
    }

    public function test_admin_ping_negado_para_role_manager_403(): void
    {
        $token = auth('api')->login(User::factory()->create(['role' => 'manager']));

        $this->getJson('/api/admin/ping', ['Authorization' => "Bearer {$token}"])
            ->assertStatus(403);
    }

    public function test_admin_ping_sem_token_retorna_401(): void
    {
        $this->getJson('/api/admin/ping')
            ->assertStatus(401);
    }

    public function test_payload_do_jwt_contem_claim_role(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $token = auth('api')->login($user);

        $parts = explode('.', $token);
        $this->assertCount(3, $parts, 'Token deve ter 3 segmentos (header.payload.signature)');

        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        $this->assertSame('admin', $payload['role'], 'Claim role deve estar no payload');
        $this->assertSame((string) $user->getKey(), (string) $payload['sub']);
    }
}

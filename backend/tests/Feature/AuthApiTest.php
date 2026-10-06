<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    private function login(?string $email = null, ?string $password = 'password'): string
    {
        $response = $this->postJson('/api/login', [
            'email' => $email ?? User::factory()->create()->email,
            'password' => $password,
        ]);
        $response->assertOk();

        return $response->json('access_token');
    }

    public function test_login_com_credenciais_validas_retorna_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in'])
            ->assertJsonPath('token_type', 'bearer');
    }

    public function test_login_com_senha_incorreta_retorna_401(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'senha-errada',
        ])->assertStatus(401);
    }

    public function test_login_com_email_inexistente_retorna_401(): void
    {
        $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ])->assertStatus(401);
    }

    public function test_login_com_payload_invalido_retorna_422(): void
    {
        $this->postJson('/api/login', ['email' => 'nao-e-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_register_cria_usuario_com_role_user_e_retorna_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Novo Usuário',
            'email' => 'novo@example.com',
            'password' => 'senha-forte-123',
            'password_confirmation' => 'senha-forte-123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.role', 'user');

        $this->assertDatabaseHas('users', ['email' => 'novo@example.com', 'role' => 'user']);

        // Login subsequente deve funcionar
        $this->postJson('/api/login', [
            'email' => 'novo@example.com',
            'password' => 'senha-forte-123',
        ])->assertOk();
    }

    public function test_register_com_email_duplicado_retorna_422(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/register', [
            'name' => 'Dup',
            'email' => $user->email,
            'password' => 'senha-forte-123',
            'password_confirmation' => 'senha-forte-123',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_com_senha_curta_retorna_422(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Curto',
            'email' => 'curto@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_me_retorna_usuario_autenticado(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $token = auth('api')->login($user);

        $this->getJson('/api/me', ['Authorization' => "Bearer {$token}"])
            ->assertOk()
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonPath('user.role', 'manager');
    }

    public function test_me_sem_token_retorna_401(): void
    {
        $this->getJson('/api/me')
            ->assertStatus(401)
            ->assertJsonStructure(['message']);
    }

    public function test_token_malformado_retorna_401(): void
    {
        $this->getJson('/api/me', ['Authorization' => 'Bearer foo.bar.baz'])
            ->assertStatus(401);
    }

    public function test_refresh_retorna_novo_token_valido(): void
    {
        $token = $this->login();

        $response = $this->postJson('/api/refresh', [], ['Authorization' => "Bearer {$token}"]);

        $response->assertOk()
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in']);

        // O token novo deve funcionar
        $this->getJson('/api/me', ['Authorization' => 'Bearer '.$response->json('access_token')])
            ->assertOk();
    }

    public function test_logout_invalida_o_token_blacklist(): void
    {
        $token = $this->login();

        $this->postJson('/api/logout', [], ['Authorization' => "Bearer {$token}"])
            ->assertOk();

        // Mesmo token não pode mais ser usado
        $this->getJson('/api/me', ['Authorization' => "Bearer {$token}"])
            ->assertStatus(401);
    }
}

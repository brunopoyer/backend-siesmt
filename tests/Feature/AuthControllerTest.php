<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_pode_fazer_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email'
                ]
            ]);
    }

    public function test_login_falha_com_credenciais_invalidas()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'senha_errada'
        ]);

        $response->assertStatus(422);
    }

    public function test_usuario_pode_se_registrar()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Novo Usuário',
            'email' => 'novo@exemplo.com',
            'password' => 'senha123'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'novo@exemplo.com'
        ]);
    }

    public function test_usuario_pode_fazer_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logout realizado com sucesso'
            ]);
    }

    public function test_usuario_pode_obter_seus_dados()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]);
    }
}

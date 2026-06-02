<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_com_credenciais_validas(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@test.com',
            'password' => bcrypt('senha123'),
            'perfil'   => 'admin',
            'ativo'    => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'test@test.com',
            'password' => 'senha123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'user', 'message']);
    }

    public function test_login_com_credenciais_invalidas(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'naoexiste@test.com',
            'password' => 'errado',
        ]);

        $response->assertStatus(422);
    }

    public function test_rota_protegida_sem_token(): void
    {
        $response = $this->getJson('/api/clientes');
        $response->assertStatus(401);
    }

    public function test_logout_invalida_token(): void
    {
        $user  = User::factory()->create(['ativo' => true]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson('/api/logout')
             ->assertStatus(200);

        // Token deve ser inválido após logout
        $this->withHeader('Authorization', "Bearer $token")
             ->getJson('/api/me')
             ->assertStatus(401);
    }
}

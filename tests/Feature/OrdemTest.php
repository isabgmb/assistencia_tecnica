<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\OrdemServico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdemTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin   = User::factory()->create(['perfil' => 'admin', 'ativo' => true]);
        $this->cliente = Cliente::factory()->create();
    }

    private function autenticado(): self
    {
        return $this->actingAs($this->admin, 'sanctum');
    }

    public function test_criar_ordem_de_servico(): void
    {
        $response = $this->autenticado()->postJson('/api/ordens', [
            'cliente_id'        => $this->cliente->id,
            'equipamento'       => 'Notebook Dell',
            'descricao_problema'=> 'Tela piscando',
            'prioridade'        => 'alta',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.equipamento', 'Notebook Dell')
                 ->assertJsonPath('data.status', 'aberta');
    }

    public function test_nao_pode_concluir_ordem_sem_tecnico(): void
    {
        $ordem = OrdemServico::create([
            'numero'            => 'OS-000001',
            'cliente_id'        => $this->cliente->id,
            'equipamento'       => 'PC',
            'descricao_problema'=> 'Problema',
            'status'            => 'aberta',
            'prioridade'        => 'media',
            'data_abertura'     => now(),
        ]);

        $response = $this->autenticado()->putJson("/api/ordens/{$ordem->id}", [
            'status' => 'concluida',
        ]);

        $response->assertStatus(422)
                 ->assertJsonPath('errors.tecnico_id.0', 'Não é possível concluir uma OS sem técnico responsável.');
    }

    public function test_nao_pode_cancelar_sem_motivo(): void
    {
        $ordem = OrdemServico::create([
            'numero'            => 'OS-000002',
            'cliente_id'        => $this->cliente->id,
            'equipamento'       => 'PC',
            'descricao_problema'=> 'Problema',
            'status'            => 'aberta',
            'prioridade'        => 'media',
            'data_abertura'     => now(),
        ]);

        $response = $this->autenticado()->putJson("/api/ordens/{$ordem->id}", [
            'status' => 'cancelada',
        ]);

        $response->assertStatus(422);
    }

    public function test_nao_pode_editar_ordem_concluida(): void
    {
        $tecnico = User::factory()->create(['perfil' => 'tecnico', 'ativo' => true]);

        $ordem = OrdemServico::create([
            'numero'            => 'OS-000003',
            'cliente_id'        => $this->cliente->id,
            'tecnico_id'        => $tecnico->id,
            'equipamento'       => 'PC',
            'descricao_problema'=> 'Problema',
            'status'            => 'concluida',
            'prioridade'        => 'media',
            'data_abertura'     => now(),
            'data_conclusao'    => now(),
        ]);

        $response = $this->autenticado()->putJson("/api/ordens/{$ordem->id}", [
            'diagnostico' => 'Tentativa de edição',
        ]);

        $response->assertStatus(422);
    }

    public function test_tecnico_nao_pode_ter_mais_de_5_ordens(): void
    {
        $tecnico = User::factory()->create(['perfil' => 'tecnico', 'ativo' => true]);

        // Criar 5 ordens em andamento para o técnico
        for ($i = 1; $i <= 5; $i++) {
            OrdemServico::create([
                'numero'            => "OS-00000{$i}",
                'cliente_id'        => $this->cliente->id,
                'tecnico_id'        => $tecnico->id,
                'equipamento'       => "Equipamento {$i}",
                'descricao_problema'=> 'Problema',
                'status'            => 'em_andamento',
                'prioridade'        => 'media',
                'data_abertura'     => now(),
            ]);
        }

        // Tentar criar a 6ª
        $response = $this->autenticado()->postJson('/api/ordens', [
            'cliente_id'        => $this->cliente->id,
            'tecnico_id'        => $tecnico->id,
            'equipamento'       => 'Notebook',
            'descricao_problema'=> 'Mais um problema',
            'prioridade'        => 'media',
        ]);

        $response->assertStatus(422)
                 ->assertJsonPath('errors.tecnico_id.0', fn($v) => str_contains($v, '5 ordens em andamento'));
    }
}

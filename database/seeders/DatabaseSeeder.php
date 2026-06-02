<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\OrdemServico;
use App\Models\Peca;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Usuários ──────────────────────────────────────────────────────────
        $admin = User::create([
            'name'     => 'Administrador',
            'email'    => 'admin@assistencia.com',
            'password' => Hash::make('password'),
            'perfil'   => 'admin',
        ]);

        $atendente = User::create([
            'name'     => 'Ana Atendente',
            'email'    => 'atendente@assistencia.com',
            'password' => Hash::make('password'),
            'perfil'   => 'atendente',
        ]);

        $tecnicos = collect();
        $nomesTecnicos = ['Carlos Silva', 'Marcos Oliveira', 'Juliana Costa'];
        foreach ($nomesTecnicos as $i => $nome) {
            $tecnicos->push(User::create([
                'name'     => $nome,
                'email'    => 'tecnico' . ($i + 1) . '@assistencia.com',
                'password' => Hash::make('password'),
                'perfil'   => 'tecnico',
            ]));
        }

        // ── Clientes ──────────────────────────────────────────────────────────
        $clientes = collect([
            ['nome' => 'João Pereira',    'cpf' => '123.456.789-00', 'telefone' => '(11) 91234-5678', 'email' => 'joao@email.com',    'cep' => '01310-100', 'cidade' => 'São Paulo',    'estado' => 'SP'],
            ['nome' => 'Maria Santos',    'cpf' => '234.567.890-11', 'telefone' => '(21) 99876-5432', 'email' => 'maria@email.com',   'cep' => '20040-020', 'cidade' => 'Rio de Janeiro','estado' => 'RJ'],
            ['nome' => 'Pedro Almeida',   'cpf' => '345.678.901-22', 'telefone' => '(31) 98765-4321', 'email' => 'pedro@email.com',   'cep' => '30112-000', 'cidade' => 'Belo Horizonte','estado' => 'MG'],
            ['nome' => 'Lucia Ferreira',  'cpf' => '456.789.012-33', 'telefone' => '(41) 97654-3210', 'email' => 'lucia@email.com',   'cep' => '80010-010', 'cidade' => 'Curitiba',      'estado' => 'PR'],
            ['nome' => 'Roberto Lima',    'cpf' => '567.890.123-44', 'telefone' => '(51) 96543-2109', 'email' => 'roberto@email.com', 'cep' => '90010-150', 'cidade' => 'Porto Alegre',  'estado' => 'RS'],
        ])->map(fn($c) => Cliente::create($c));

        // ── Peças ─────────────────────────────────────────────────────────────
        $pecas = collect([
            ['codigo' => 'HD-500GB',  'nome' => 'HD SATA 500GB',         'estoque' => 10, 'estoque_minimo' => 2, 'preco_custo' => 150.00, 'preco_venda' => 280.00],
            ['codigo' => 'RAM-8GB',   'nome' => 'Memória RAM DDR4 8GB',  'estoque' => 15, 'estoque_minimo' => 3, 'preco_custo' =>  90.00, 'preco_venda' => 180.00],
            ['codigo' => 'FONTE-ATX', 'nome' => 'Fonte ATX 500W',        'estoque' =>  8, 'estoque_minimo' => 2, 'preco_custo' => 180.00, 'preco_venda' => 320.00],
            ['codigo' => 'PASTA-TER', 'nome' => 'Pasta Térmica 3g',      'estoque' => 30, 'estoque_minimo' => 5, 'preco_custo' =>   8.00, 'preco_venda' =>  25.00],
            ['codigo' => 'VENTOINHA','nome'  => 'Ventoinha CPU 92mm',    'estoque' => 12, 'estoque_minimo' => 2, 'preco_custo' =>  25.00, 'preco_venda' =>  60.00],
            ['codigo' => 'TELA-15',  'nome'  => 'Tela Notebook 15.6"',   'estoque' =>  5, 'estoque_minimo' => 1, 'preco_custo' => 350.00, 'preco_venda' => 650.00],
            ['codigo' => 'BATT-NOT', 'nome'  => 'Bateria Notebook 6 Células', 'estoque' => 6, 'estoque_minimo' => 1, 'preco_custo' => 120.00, 'preco_venda' => 250.00],
        ])->map(fn($p) => Peca::create($p));

        // ── Ordens de Serviço ─────────────────────────────────────────────────
        $ordens = [
            ['cliente_id' => $clientes[0]->id, 'tecnico_id' => $tecnicos[0]->id, 'equipamento' => 'Notebook Dell Inspiron', 'descricao_problema' => 'Não liga, sem imagem na tela.', 'status' => 'em_andamento', 'prioridade' => 'alta',    'data_previsao' => now()->addDays(2), 'valor_servico' => 250.00],
            ['cliente_id' => $clientes[1]->id, 'tecnico_id' => $tecnicos[0]->id, 'equipamento' => 'Desktop HP Pavillion',   'descricao_problema' => 'Computador reiniciando sozinho.',  'status' => 'aberta',       'prioridade' => 'media',   'data_previsao' => now()->addDays(3), 'valor_servico' => null],
            ['cliente_id' => $clientes[2]->id, 'tecnico_id' => $tecnicos[1]->id, 'equipamento' => 'iPhone 13 Pro',          'descricao_problema' => 'Tela quebrada.',                    'status' => 'aguardando_peca','prioridade' => 'urgente','data_previsao' => now()->addDay(),  'valor_servico' => 800.00],
            ['cliente_id' => $clientes[3]->id, 'tecnico_id' => $tecnicos[1]->id, 'equipamento' => 'Impressora Epson L3150', 'descricao_problema' => 'Não imprime, erro de cabeça.',     'status' => 'aberta',       'prioridade' => 'baixa',   'data_previsao' => now()->addWeek(), 'valor_servico' => 120.00],
            ['cliente_id' => $clientes[4]->id, 'tecnico_id' => $tecnicos[2]->id, 'equipamento' => 'Notebook Lenovo',        'descricao_problema' => 'Bateria não carrega.',              'status' => 'concluida',    'prioridade' => 'media',   'data_previsao' => now()->subDay(),  'valor_servico' => 200.00, 'data_conclusao' => now()->subHours(2)],
        ];

        foreach ($ordens as $dadosOrdem) {
            $dadosOrdem['data_abertura'] = now()->subDays(rand(1, 10));
            OrdemServico::create($dadosOrdem);
        }

        $this->command->info('✅ Seed concluído com sucesso!');
        $this->command->table(
            ['Perfil', 'E-mail', 'Senha'],
            [
                ['Admin',     'admin@assistencia.com',     'password'],
                ['Atendente', 'atendente@assistencia.com', 'password'],
                ['Técnico 1', 'tecnico1@assistencia.com',  'password'],
                ['Técnico 2', 'tecnico2@assistencia.com',  'password'],
                ['Técnico 3', 'tecnico3@assistencia.com',  'password'],
            ]
        );
    }
}

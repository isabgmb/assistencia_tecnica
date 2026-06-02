<?php

namespace App\Http\Services;

use App\Http\Repositories\ClienteRepository;
use App\Models\Cliente;
use Illuminate\Support\Facades\Http;

class ClienteService
{
    public function __construct(private ClienteRepository $repo) {}

    public function listar(array $filtros): mixed
    {
        return $this->repo->buscar($filtros);
    }

    public function criar(array $dados): Cliente
    {
        if (!empty($dados['cep'])) {
            $dados = array_merge($dados, $this->buscarEnderecoPorCep($dados['cep']));
        }
        return $this->repo->criar($dados);
    }

    public function atualizar(Cliente $cliente, array $dados): Cliente
    {
        if (!empty($dados['cep']) && $dados['cep'] !== $cliente->cep) {
            $dados = array_merge($dados, $this->buscarEnderecoPorCep($dados['cep']));
        }
        return $this->repo->atualizar($cliente, $dados);
    }

    public function buscarEnderecoPorCep(string $cep): array
    {
        $cep = preg_replace('/\D/', '', $cep);

        try {
            $response = Http::timeout(5)
                ->get("https://viacep.com.br/ws/{$cep}/json/");

            if ($response->successful()) {
                $data = $response->json();
                if (!isset($data['erro'])) {
                    return [
                        'logradouro' => $data['logradouro'] ?? null,
                        'bairro'     => $data['bairro'] ?? null,
                        'cidade'     => $data['localidade'] ?? null,
                        'estado'     => $data['uf'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Falha silenciosa; endereço manual será aceito
        }

        return [];
    }
}

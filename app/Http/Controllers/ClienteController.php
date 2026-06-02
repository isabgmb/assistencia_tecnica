<?php

namespace App\Http\Controllers;

use App\Http\Services\ClienteService;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function __construct(private ClienteService $service) {}

    public function index(Request $request): JsonResponse
    {
        $clientes = $this->service->listar($request->only(['busca', 'ativo']));
        return response()->json($clientes);
    }

    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'nome'        => 'required|string|max:255',
            'cpf'         => 'required|string|size:14|unique:clientes,cpf',
            'telefone'    => 'required|string|max:20',
            'email'       => 'nullable|email|unique:clientes,email',
            'cep'         => 'nullable|string|max:9',
            'logradouro'  => 'nullable|string|max:255',
            'numero'      => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:100',
            'bairro'      => 'nullable|string|max:100',
            'cidade'      => 'nullable|string|max:100',
            'estado'      => 'nullable|string|size:2',
        ]);

        $cliente = $this->service->criar($dados);

        return response()->json([
            'status'  => 201,
            'message' => 'Cliente cadastrado com sucesso.',
            'data'    => $cliente,
        ], 201);
    }

    public function show(Cliente $cliente): JsonResponse
    {
        return response()->json($cliente->load('ordens'));
    }

    public function update(Request $request, Cliente $cliente): JsonResponse
    {
        $dados = $request->validate([
            'nome'        => 'sometimes|string|max:255',
            'cpf'         => "sometimes|string|size:14|unique:clientes,cpf,{$cliente->id}",
            'telefone'    => 'sometimes|string|max:20',
            'email'       => "sometimes|email|unique:clientes,email,{$cliente->id}",
            'cep'         => 'nullable|string|max:9',
            'logradouro'  => 'nullable|string|max:255',
            'numero'      => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:100',
            'bairro'      => 'nullable|string|max:100',
            'cidade'      => 'nullable|string|max:100',
            'estado'      => 'nullable|string|size:2',
            'ativo'       => 'boolean',
        ]);

        $cliente = $this->service->atualizar($cliente, $dados);

        return response()->json([
            'status'  => 200,
            'message' => 'Cliente atualizado.',
            'data'    => $cliente,
        ]);
    }

    public function cep(string $cep): JsonResponse
    {
        $endereco = $this->service->buscarEnderecoPorCep($cep);

        if (empty($endereco)) {
            return response()->json(['message' => 'CEP não encontrado.'], 404);
        }

        return response()->json($endereco);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Services\PecaService;
use App\Models\OrdemServico;
use App\Models\Peca;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PecaController extends Controller
{
    public function __construct(private PecaService $service) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->service->listar($request->only(['busca', 'ativo', 'sem_estoque'])));
    }

    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'codigo'         => 'required|string|unique:pecas,codigo',
            'nome'           => 'required|string|max:255',
            'descricao'      => 'nullable|string',
            'estoque'        => 'required|integer|min:0',
            'estoque_minimo' => 'nullable|integer|min:0',
            'preco_custo'    => 'nullable|numeric|min:0',
            'preco_venda'    => 'required|numeric|min:0',
        ]);

        $peca = $this->service->criar($dados);

        return response()->json([
            'status'  => 201,
            'message' => 'Peça cadastrada com sucesso.',
            'data'    => $peca,
        ], 201);
    }

    public function show(Peca $peca): JsonResponse
    {
        return response()->json($peca->load('movimentacoes'));
    }

    public function update(Request $request, Peca $peca): JsonResponse
    {
        $dados = $request->validate([
            'nome'           => 'sometimes|string|max:255',
            'descricao'      => 'nullable|string',
            'estoque_minimo' => 'nullable|integer|min:0',
            'preco_custo'    => 'nullable|numeric|min:0',
            'preco_venda'    => 'sometimes|numeric|min:0',
            'ativo'          => 'boolean',
        ]);

        $peca = $this->service->atualizar($peca, $dados);

        return response()->json(['status' => 200, 'message' => 'Peça atualizada.', 'data' => $peca]);
    }

    public function movimentar(Request $request, Peca $peca): JsonResponse
    {
        $dados = $request->validate([
            'tipo'       => 'required|in:entrada,saida,ajuste',
            'quantidade' => 'required|integer|min:1',
            'observacao' => 'nullable|string',
        ]);

        $peca = $this->service->movimentarEstoque(
            $peca, $dados['tipo'], $dados['quantidade'], $dados['observacao'] ?? null
        );

        return response()->json([
            'status'  => 200,
            'message' => 'Estoque atualizado.',
            'data'    => $peca,
        ]);
    }

    public function vincularOrdem(Request $request, OrdemServico $ordemServico): JsonResponse
    {
        $dados = $request->validate([
            'peca_id'    => 'required|exists:pecas,id',
            'quantidade' => 'required|integer|min:1',
        ]);

        $ordemPeca = $this->service->vincularPecaOrdem(
            $ordemServico, $dados['peca_id'], $dados['quantidade']
        );

        return response()->json([
            'status'  => 201,
            'message' => 'Peça vinculada à OS com sucesso.',
            'data'    => $ordemPeca,
        ], 201);
    }

    public function maisUtilizadas(): JsonResponse
    {
        return response()->json($this->service->maisUtilizadas());
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Services\OrdemService;
use App\Models\OrdemServico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdemController extends Controller
{
    public function __construct(private OrdemService $service) {}

    public function index(Request $request): JsonResponse
    {
        $ordens = $this->service->listar($request->only([
            'status', 'prioridade', 'tecnico_id', 'cliente_id', 'numero',
        ]));
        return response()->json($ordens);
    }

    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'cliente_id'       => 'required|exists:clientes,id',
            'tecnico_id'       => 'nullable|exists:users,id',
            'equipamento'      => 'required|string|max:255',
            'marca'            => 'nullable|string|max:100',
            'modelo'           => 'nullable|string|max:100',
            'numero_serie'     => 'nullable|string|max:100',
            'descricao_problema' => 'required|string',
            'prioridade'       => 'required|in:baixa,media,alta,urgente',
            'data_previsao'    => 'nullable|date|after:now',
            'valor_servico'    => 'nullable|numeric|min:0',
        ]);

        $ordem = $this->service->criar($dados);

        return response()->json([
            'status'  => 201,
            'message' => 'Ordem de serviço aberta com sucesso.',
            'data'    => $ordem,
        ], 201);
    }

    public function show(OrdemServico $ordemServico): JsonResponse
    {
        return response()->json(
            $this->service->buscarPorId($ordemServico->id)
        );
    }

    public function update(Request $request, OrdemServico $ordemServico): JsonResponse
    {
        $dados = $request->validate([
            'tecnico_id'          => 'nullable|exists:users,id',
            'status'              => 'sometimes|in:aberta,em_andamento,aguardando_peca,concluida,cancelada',
            'prioridade'          => 'sometimes|in:baixa,media,alta,urgente',
            'diagnostico'         => 'nullable|string',
            'solucao'             => 'nullable|string',
            'data_previsao'       => 'nullable|date',
            'valor_servico'       => 'nullable|numeric|min:0',
            'motivo_cancelamento' => 'nullable|string',
            'observacao'          => 'nullable|string',
        ]);

        $ordem = $this->service->atualizar($ordemServico, $dados);

        return response()->json([
            'status'  => 200,
            'message' => 'Ordem de serviço atualizada.',
            'data'    => $ordem,
        ]);
    }

    public function relatorio(): JsonResponse
    {
        return response()->json($this->service->relatorios());
    }
}

<?php

namespace App\Http\Repositories;

use App\Models\OrdemServico;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrdemRepository
{
    public function listar(array $filtros): LengthAwarePaginator
    {
        $query = OrdemServico::with(['cliente', 'tecnico']);

        if (!empty($filtros['status']))      $query->where('status', $filtros['status']);
        if (!empty($filtros['prioridade']))  $query->where('prioridade', $filtros['prioridade']);
        if (!empty($filtros['tecnico_id']))  $query->where('tecnico_id', $filtros['tecnico_id']);
        if (!empty($filtros['cliente_id']))  $query->where('cliente_id', $filtros['cliente_id']);
        if (!empty($filtros['numero']))      $query->where('numero', 'like', "%{$filtros['numero']}%");

        $ordem = match($filtros['prioridade_sort'] ?? '') {
            'asc'  => 'asc',
            default => 'desc',
        };

        $query->orderByRaw("CASE prioridade WHEN 'urgente' THEN 1 WHEN 'alta' THEN 2 WHEN 'media' THEN 3 WHEN 'baixa' THEN 4 END")
      ->orderBy('data_abertura', 'desc');

        return $query->paginate(15);
    }

    public function criar(array $dados): OrdemServico
    {
        $dados['data_abertura'] = now();
        return OrdemServico::create($dados);
    }

    public function buscarPorId(int $id): ?OrdemServico
    {
        return OrdemServico::with(['cliente', 'tecnico', 'historico.usuario', 'pecas.peca'])->find($id);
    }

    public function atualizar(OrdemServico $ordem, array $dados): OrdemServico
    {
        $ordem->update($dados);
        return OrdemServico::findOrFail($ordem->id);
   }

    public function quantidadePorStatus(): array
    {
        return OrdemServico::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    public function ordensEmAtraso(): \Illuminate\Database\Eloquent\Collection
    {
        return OrdemServico::with(['cliente', 'tecnico'])
            ->whereNotIn('status', ['concluida', 'cancelada'])
            ->whereNotNull('data_previsao')
            ->where('data_previsao', '<', now())
            ->orderBy('data_previsao')
            ->get();
    }

    public function tecnicosComMaisAtendimentos(int $limit = 10): \Illuminate\Support\Collection
    {
        return OrdemServico::select('tecnico_id', DB::raw('count(*) as total'))
            ->with('tecnico:id,name,email')
            ->whereNotNull('tecnico_id')
            ->groupBy('tecnico_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    public function tempoMedioAtendimento(): float
    {
        $result = OrdemServico::whereNotNull('data_conclusao')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, data_abertura, data_conclusao)) as media_minutos')
            ->value('media_minutos');

        return round(($result ?? 0) / 60, 2);
    }
}

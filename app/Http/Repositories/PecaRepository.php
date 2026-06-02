<?php

namespace App\Http\Repositories;

use App\Models\MovimentacaoEstoque;
use App\Models\OrdemPeca;
use App\Models\Peca;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PecaRepository
{
    public function listar(array $filtros): LengthAwarePaginator
    {
        $query = Peca::query();

        if (!empty($filtros['busca'])) {
            $busca = $filtros['busca'];
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%$busca%")
                  ->orWhere('codigo', 'like', "%$busca%");
            });
        }

        if (isset($filtros['ativo'])) $query->where('ativo', $filtros['ativo']);
        if (isset($filtros['sem_estoque']) && $filtros['sem_estoque']) {
            $query->where('estoque', '<=', 0);
        }

        return $query->orderBy('nome')->paginate(15);
    }

    public function criar(array $dados): Peca
    {
        return Peca::create($dados);
    }

    public function atualizar(Peca $peca, array $dados): Peca
    {
        $peca->update($dados);
        return $peca->fresh();
    }

    public function buscarPorId(int $id): ?Peca
    {
        return Peca::find($id);
    }

    public function registrarMovimentacao(array $dados): MovimentacaoEstoque
    {
        return MovimentacaoEstoque::create($dados);
    }

    public function vincularPecaOrdem(array $dados): OrdemPeca
    {
        return OrdemPeca::create($dados);
    }

    public function maisUtilizadas(int $limit = 10): \Illuminate\Support\Collection
    {
        return OrdemPeca::select('peca_id', DB::raw('SUM(quantidade) as total_utilizado'))
            ->with('peca:id,codigo,nome')
            ->groupBy('peca_id')
            ->orderByDesc('total_utilizado')
            ->limit($limit)
            ->get();
    }
}

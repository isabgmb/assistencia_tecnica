<?php

namespace App\Http\Services;

use App\Http\Repositories\PecaRepository;
use App\Models\OrdemServico;
use App\Models\Peca;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PecaService
{
    public function __construct(private PecaRepository $repo) {}

    public function listar(array $filtros): mixed
    {
        return $this->repo->listar($filtros);
    }

    public function criar(array $dados): Peca
    {
        $peca = $this->repo->criar($dados);

        if ($peca->estoque > 0) {
            $this->repo->registrarMovimentacao([
                'peca_id'          => $peca->id,
                'usuario_id'       => Auth::id(),
                'tipo'             => 'entrada',
                'quantidade'       => $peca->estoque,
                'estoque_anterior' => 0,
                'estoque_posterior'=> $peca->estoque,
                'observacao'       => 'Estoque inicial',
            ]);
        }

        return $peca;
    }

    public function atualizar(Peca $peca, array $dados): Peca
    {
        return $this->repo->atualizar($peca, $dados);
    }

    public function movimentarEstoque(Peca $peca, string $tipo, int $quantidade, string $observacao = null, ?int $ordemId = null): Peca
    {
        DB::transaction(function () use ($peca, $tipo, $quantidade, $observacao, $ordemId) {
            $estoqueAnterior = $peca->estoque;

            if ($tipo === 'saida') {
                if ($peca->estoque < $quantidade) {
                    throw ValidationException::withMessages([
                        'quantidade' => ["Estoque insuficiente. Disponível: {$peca->estoque}, solicitado: {$quantidade}."],
                    ]);
                }
                $peca->estoque -= $quantidade;
            } elseif ($tipo === 'entrada') {
                $peca->estoque += $quantidade;
            } elseif ($tipo === 'ajuste') {
                if ($quantidade < 0) {
                    throw ValidationException::withMessages([
                        'quantidade' => ['Estoque não pode ficar negativo.'],
                    ]);
                }
                $peca->estoque = $quantidade;
            }

            $peca->save();

            $this->repo->registrarMovimentacao([
                'peca_id'          => $peca->id,
                'usuario_id'       => Auth::id(),
                'ordem_id'         => $ordemId,
                'tipo'             => $tipo,
                'quantidade'       => $quantidade,
                'estoque_anterior' => $estoqueAnterior,
                'estoque_posterior'=> $peca->estoque,
                'observacao'       => $observacao,
            ]);
        });

        return $peca->fresh();
    }

    public function vincularPecaOrdem(OrdemServico $ordem, int $pecaId, int $quantidade): mixed
    {
        if ($ordem->isConcluida() || $ordem->isCancelada()) {
            throw ValidationException::withMessages([
                'ordem_id' => ['Não é possível adicionar peças em OS concluída ou cancelada.'],
            ]);
        }

        $peca = $this->repo->buscarPorId($pecaId);

        if (!$peca || !$peca->ativo) {
            throw ValidationException::withMessages([
                'peca_id' => ['Peça não encontrada ou inativa.'],
            ]);
        }

        if (!$peca->temEstoque($quantidade)) {
            throw ValidationException::withMessages([
                'quantidade' => ["Peça sem estoque suficiente. Disponível: {$peca->estoque}."],
            ]);
        }

        $ordemPeca = null;

        DB::transaction(function () use ($ordem, $peca, $quantidade, &$ordemPeca) {
            $ordemPeca = $this->repo->vincularPecaOrdem([
                'ordem_id'       => $ordem->id,
                'peca_id'        => $peca->id,
                'quantidade'     => $quantidade,
                'preco_unitario' => $peca->preco_venda,
            ]);

            $this->movimentarEstoque(
                $peca, 'saida', $quantidade,
                "Utilizada na OS #{$ordem->numero}",
                $ordem->id
            );
        });

        return $ordemPeca->load('peca');
    }

    public function maisUtilizadas(): mixed
    {
        return $this->repo->maisUtilizadas();
    }
}

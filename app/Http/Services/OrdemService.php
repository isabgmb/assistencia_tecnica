<?php

namespace App\Http\Services;

use App\Http\Repositories\OrdemRepository;
use App\Models\HistoricoOrdem;
use App\Models\OrdemServico;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OrdemService
{
    public function __construct(private OrdemRepository $repo) {}

    public function listar(array $filtros): mixed
    {
        return $this->repo->listar($filtros);
    }

    public function criar(array $dados): OrdemServico
    {
        if (!empty($dados['tecnico_id'])) {
            $this->validarCapacidadeTecnico($dados['tecnico_id']);
        }

        $ordem = $this->repo->criar($dados);

        $this->registrarHistorico($ordem, null, $ordem->status, 'Ordem de serviço criada.');

        return $this->repo->buscarPorId($ordem->id);
    }

    public function atualizar(OrdemServico $ordem, array $dados): OrdemServico
    {
        if ($ordem->isConcluida() || $ordem->isCancelada()) {
            throw ValidationException::withMessages([
                'status' => ['Ordens concluídas ou canceladas não podem ser editadas.'],
            ]);
        }

        $statusAnterior = $ordem->status;
        $novoStatus     = $dados['status'] ?? $statusAnterior;

        // Regra: não pode concluir sem técnico
        if ($novoStatus === OrdemServico::STATUS_CONCLUIDA) {
            $tecnicoId = $dados['tecnico_id'] ?? $ordem->tecnico_id;
            if (!$tecnicoId) {
                throw ValidationException::withMessages([
                    'tecnico_id' => ['Não é possível concluir uma OS sem técnico responsável.'],
                ]);
            }
            $dados['data_conclusao'] = now();
        }

        // Regra: cancelamento exige motivo
        if ($novoStatus === OrdemServico::STATUS_CANCELADA) {
            if (empty($dados['motivo_cancelamento'])) {
                throw ValidationException::withMessages([
                    'motivo_cancelamento' => ['Informe o motivo do cancelamento.'],
                ]);
            }
        }

        // Regra: troca de técnico valida capacidade
        if (!empty($dados['tecnico_id']) && $dados['tecnico_id'] != $ordem->tecnico_id) {
            $this->validarCapacidadeTecnico($dados['tecnico_id'], $ordem->id);
        }

        $ordem = $this->repo->atualizar($ordem, $dados);

        if ($statusAnterior !== $novoStatus) {
            $obs = $dados['observacao'] ?? null;
            if ($novoStatus === OrdemServico::STATUS_CANCELADA) {
                $obs = $dados['motivo_cancelamento'];
            }
            $this->registrarHistorico($ordem, $statusAnterior, $novoStatus, $obs);
        }

        return $this->repo->buscarPorId($ordem->id);
    }

    public function buscarPorId(int $id): ?OrdemServico
    {
        return $this->repo->buscarPorId($id);
    }

    public function relatorios(): array
    {
        return [
            'por_status'           => $this->repo->quantidadePorStatus(),
            'em_atraso'            => $this->repo->ordensEmAtraso(),
            'tecnicos_atendimentos'=> $this->repo->tecnicosComMaisAtendimentos(),
            'tempo_medio_horas'    => $this->repo->tempoMedioAtendimento(),
        ];
    }

    private function validarCapacidadeTecnico(int $tecnicoId, ?int $ordemAtualId = null): void
    {
        $tecnico = User::findOrFail($tecnicoId);

        if (!$tecnico->isTecnico()) {
            throw ValidationException::withMessages([
                'tecnico_id' => ['O usuário selecionado não é técnico.'],
            ]);
        }

        $query = $tecnico->ordensComoTecnico()
            ->whereIn('status', ['aberta', 'em_andamento']);

        if ($ordemAtualId) {
            $query->where('id', '!=', $ordemAtualId);
        }

        if ($query->count() >= 5) {
            throw ValidationException::withMessages([
                'tecnico_id' => ["O técnico {$tecnico->name} já possui 5 ordens em andamento."],
            ]);
        }
    }

    private function registrarHistorico(OrdemServico $ordem, ?string $anterior, string $novo, ?string $obs): void
    {
        HistoricoOrdem::create([
            'ordem_id'        => $ordem->id,
            'usuario_id'      => Auth::id(),
            'status_anterior' => $anterior,
            'status_novo'     => $novo,
            'observacao'      => $obs,
        ]);
    }
}

<?php

namespace App\Http\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function listar(array $filtros = []): LengthAwarePaginator
    {
        $query = User::query();
        if (!empty($filtros['perfil'])) $query->where('perfil', $filtros['perfil']);
        if (isset($filtros['ativo']))   $query->where('ativo', $filtros['ativo']);
        return $query->orderBy('name')->paginate(15);
    }

    public function criar(array $dados): User
    {
        return User::create($dados);
    }

    public function atualizar(User $user, array $dados): User
    {
        $user->update($dados);
        return $user->fresh();
    }

    public function buscarPorId(int $id): ?User
    {
        return User::find($id);
    }

    public function tecnicosDisponiveis(): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('perfil', 'tecnico')
            ->where('ativo', true)
            ->withCount([
                'ordensComoTecnico as ordens_ativas' => function ($q) {
                    $q->whereIn('status', ['aberta', 'em_andamento']);
                }
            ])
            ->having('ordens_ativas', '<', 5)
            ->get();
    }
}

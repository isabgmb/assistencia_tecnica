<?php

namespace App\Http\Repositories;

use App\Models\Cliente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClienteRepository
{
    public function buscar(array $filtros): LengthAwarePaginator
    {
        $query = Cliente::query();

        if (!empty($filtros['busca'])) {
            $busca = $filtros['busca'];
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%$busca%")
                  ->orWhere('cpf', 'like', "%$busca%")
                  ->orWhere('email', 'like', "%$busca%")
                  ->orWhere('telefone', 'like', "%$busca%");
            });
        }

        if (isset($filtros['ativo'])) {
            $query->where('ativo', $filtros['ativo']);
        }

        return $query->orderBy('nome')->paginate(15);
    }

    public function criar(array $dados): Cliente
    {
        return Cliente::create($dados);
    }

    public function atualizar(Cliente $cliente, array $dados): Cliente
    {
        $cliente->update($dados);
        return $cliente->fresh();
    }

    public function buscarPorId(int $id): ?Cliente
    {
        return Cliente::find($id);
    }
}

<?php

namespace App\Http\Services;

use App\Http\Repositories\UserRepository;
use App\Models\User;

class UserService
{
    public function __construct(private UserRepository $repo) {}

    public function listar(array $filtros): mixed
    {
        return $this->repo->listar($filtros);
    }

    public function criar(array $dados): User
    {
        return $this->repo->criar($dados);
    }

    public function atualizar(User $user, array $dados): User
    {
        return $this->repo->atualizar($user, $dados);
    }

    public function tecnicosDisponiveis(): mixed
    {
        return $this->repo->tecnicosDisponiveis();
    }
}

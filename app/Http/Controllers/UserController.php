<?php

namespace App\Http\Controllers;

use App\Http\Services\UserService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserService $service) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->service->listar($request->only(['perfil', 'ativo'])));
    }

    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'perfil'   => 'required|in:admin,tecnico,atendente',
        ]);

        $user = $this->service->criar($dados);

        return response()->json([
            'status'  => 201,
            'message' => 'Usuário criado com sucesso.',
            'data'    => $user,
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $dados = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => "sometimes|email|unique:users,email,{$user->id}",
            'password' => 'sometimes|string|min:8|confirmed',
            'perfil'   => 'sometimes|in:admin,tecnico,atendente',
            'ativo'    => 'sometimes|boolean',
        ]);

        $user = $this->service->atualizar($user, $dados);

        return response()->json(['status' => 200, 'message' => 'Usuário atualizado.', 'data' => $user]);
    }

    public function tecnicosDisponiveis(): JsonResponse
    {
        return response()->json($this->service->tecnicosDisponiveis());
    }
}

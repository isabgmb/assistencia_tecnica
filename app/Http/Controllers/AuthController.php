<?php

namespace App\Http\Controllers;

use App\Http\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $service) {}

    /**
     * @OA\Post(
     *   path="/api/login",
     *   summary="Autenticar usuário",
     *   tags={"Autenticação"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     required={"email","password"},
     *     @OA\Property(property="email", type="string", example="admin@assistencia.com"),
     *     @OA\Property(property="password", type="string", example="password")
     *   )),
     *   @OA\Response(response=200, description="Login realizado com sucesso")
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $result = $this->service->login($request->email, $request->password);

        return response()->json([
            'status'  => 200,
            'message' => 'Login realizado com sucesso.',
            'user'    => $result['user'],
            'token'   => $result['token'],
        ]);
    }

    /**
     * @OA\Post(
     *   path="/api/logout",
     *   summary="Encerrar sessão",
     *   tags={"Autenticação"},
     *   security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="Logout realizado")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $this->service->logout($request->user());

        return response()->json(['status' => 200, 'message' => 'Logout realizado com sucesso.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}

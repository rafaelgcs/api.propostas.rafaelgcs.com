<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login
     * 
     * @group Auth
     * @bodyParam email string required The e-mail for login. Example: test@ejcet.com.br
     * @bodyParam password string required The password of the user. Example: %&Ghasd@!$
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            "email" => 'required|email',
            "password" => 'required'
        ]);
        try {
            $toLogin = $request->all();
            $user = User::select('*')
                ->where('email', '=', $toLogin['email'])
                ->first();

            if ($user) {
                if (Hash::check($toLogin['password'], $user->password)) {
                    $credentials = $request->only(['email', 'password']);

                    if (!$token = Auth::attempt($credentials)) {
                        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
                    }
                    return $this->respondWithTokenAndUser($token, $user);
                }
                return response()->json(["success" => false, "data" => [], "error" => [], "message" => "Usuário ou senha incorretos!"]);
            }
            return response()->json(["success" => false, "data" => [], "error" => ["code" => "L-001", "message" => "Não foi encontrado nenhum usuário com o e-mail fornecido"], "message" => "Usuário não encontrado!"]);
        } catch (Exception $e) {
            return response()->json(["success" => false, "data" => [], "error" => ["code" => $e->getCode(), "message" => $e->getMessage()], "message" => "Tivemos uns problemas técnicos, sinalize um administrador/técnico do sistema!"]);
        }
    }

    /**
     * Get the authenticated User.
     * 
     * @group Auth
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }
    
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]
        ]);
    }
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithTokenAndUser($token, $user)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'user' => $user,
                'expires_in' => auth()->factory()->getTTL() * 60
            ]
        ]);
    }
}

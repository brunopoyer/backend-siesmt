<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        \Log::info('Iniciando login');

        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
            \Log::info('Validação OK');

            if (!Auth::attempt($request->only('email', 'password'))) {
                \Log::warning('Credenciais inválidas');
                throw ValidationException::withMessages([
                    'email' => ['As credenciais fornecidas estão incorretas.'],
                ]);
            }
            \Log::info('Autenticação OK');

            $user = User::where('email', $request->email)->first();
            $token = $user->createToken('auth-token')->plainTextToken;
            \Log::info('Token gerado com sucesso');

            return response()->json([
                'token' => $token,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro no login: ' . $e->getMessage());
            throw $e;
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout realizado com sucesso']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function register(Request $request)
    {
        \Log::info('Iniciando registro');

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);

            \Log::info('Validação OK');

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('auth-token')->plainTextToken;
            \Log::info('Usuário registrado com sucesso');

            return response()->json([
                'token' => $token,
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Erro no registro: ' . $e->getMessage());
            throw $e;
        }
    }
}

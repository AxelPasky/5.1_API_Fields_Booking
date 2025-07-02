<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Tenta di autenticare l'utente
        if (!Auth::attempt($credentials)) {
            // Se le credenziali non sono valide, restituisce 401 Unauthorized
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Se l'autenticazione ha successo, crea e restituisce il token
        $user = $request->user();
        $token = $user->createToken('auth-token')->accessToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}

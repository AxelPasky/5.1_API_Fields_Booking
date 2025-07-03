<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Cerca l'utente tramite email
        $user = User::where('email', $request->email)->first();

        // Verifica che l'utente esista e che la password sia corretta
        if (! $user || ! Hash::check($request->password, $user->password)) {
            // Invece di lanciare un'eccezione, restituisci una risposta 401
            return response()->json(['message' => 'Le credenziali fornite non sono corrette.'], 401);
        }

        // Se le credenziali sono corrette, crea e restituisce il token
        $token = $user->createToken('auth-token')->accessToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function register(Request $request)
    {
        // 1. Valida i dati in ingresso
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2. Crea il nuovo utente
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assegna il ruolo di default 'User'
        $user->assignRole('User');

        // 3. Crea un token di accesso per il nuovo utente
        $token = $user->createToken('auth-token')->accessToken;

        // 4. Restituisci la risposta con il token e lo stato 201
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }
}

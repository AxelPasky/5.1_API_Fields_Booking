<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Crea una nuova richiesta interna al server OAuth per ottenere il token
        $tokenRequest = Request::create(
            'oauth/token',
            'POST',
            [
                'grant_type' => 'password',
                'client_id' => env('PASSPORT_PASSWORD_GRANT_CLIENT_ID'),
                'client_secret' => env('PASSPORT_PASSWORD_GRANT_CLIENT_SECRET'),
                'username' => $credentials['email'],
                'password' => $credentials['password'],
                'scope' => '',
            ]
        );

        // Esegui la richiesta e ottieni la risposta
        $response = Route::dispatch($tokenRequest);

        // Restituisci la risposta JSON (che contiene access_token, expires_in, ecc.)
        return $response;
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FieldResource;
use App\Models\Field;
use Illuminate\Http\Request;

class FieldController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Se l'utente è un admin, mostra tutti i campi.
        // Altrimenti, mostra solo quelli disponibili.
        if ($user->hasRole('Admin')) {
            $fields = Field::all();
        } else {
            $fields = Field::where('is_available', true)->get();
        }

        // Usa la risorsa per formattare la collezione di campi
        return FieldResource::collection($fields);
    }
}

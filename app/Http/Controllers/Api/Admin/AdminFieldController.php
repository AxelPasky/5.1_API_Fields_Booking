<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\FieldResource;
use App\Models\Field;
use Illuminate\Http\Request;

class AdminFieldController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'price_per_hour' => 'required|numeric|min:0', // <-- Aggiungi questa regola
            'is_available' => 'required|boolean',
        ]);

        $field = Field::create($validatedData);

        return (new FieldResource($field))
                ->response()
                ->setStatusCode(201); // Imposta lo status code a 201 Created
    }
}

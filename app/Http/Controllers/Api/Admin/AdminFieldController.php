<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\FieldResource;
use App\Models\Field;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminFieldController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' =>  ['required', Rule::in(['tennis', 'padel', 'football', 'basket'])],
            'price_per_hour' => 'required|numeric|min:0',
            'is_available' => 'required|boolean',
        ]);

        $field = Field::create($validatedData);

        return (new FieldResource($field))
                ->response()
                ->setStatusCode(201);
    }

    public function update(Request $request, Field $field)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => ['sometimes', 'required', Rule::in(['tennis', 'padel', 'football', 'basket'])],
            'price_per_hour' => 'sometimes|required|numeric|min:0',
            'is_available' => 'sometimes|required|boolean',
        ]);

        $field->update($validatedData);

        return new FieldResource($field);
    }
}

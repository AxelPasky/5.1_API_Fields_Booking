<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\FieldResource;
use App\Models\Field;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @group Admin
 * Endpoints for managing fields (create, update, delete) as an admin.
 */
class AdminFieldController extends Controller
{
    /**
     * Create field
     *
     * Allows admins to add a new field.
     */
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

    /**
     * Update field
     *
     * Allows admins to update field details.
     */
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

    /**
     * Delete field
     *
     * Allows admins to remove a field from the system.
     */
    public function destroy(Field $field)
    {
        $field->delete();

        return response()->noContent();
    }
}

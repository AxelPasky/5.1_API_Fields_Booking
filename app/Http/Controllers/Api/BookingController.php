<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Field;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'field_id' => 'required|exists:fields,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        $field = Field::findOrFail($validatedData['field_id']);

        // Semplice controllo di disponibilità per ora
        if (!$field->is_available) {
            throw ValidationException::withMessages([
                'field_id' => 'The selected field is not available for booking.',
            ]);
        }

        $start = Carbon::parse($validatedData['start_time']);
        $end = Carbon::parse($validatedData['end_time']);
        $durationInHours = $start->diffInMinutes($end) / 60;
        $totalPrice = $durationInHours * $field->price_per_hour;

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'field_id' => $field->id,
            'start_time' => $start,
            'end_time' => $end,
            'total_price' => $totalPrice,
            'status' => 'confirmed', // Per ora confermiamo direttamente
        ]);

        return (new BookingResource($booking))
            ->response()
            ->setStatusCode(201);
    }
}

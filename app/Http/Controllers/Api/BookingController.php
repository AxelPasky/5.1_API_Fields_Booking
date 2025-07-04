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
        $start = Carbon::parse($validatedData['start_time']);
        $end = Carbon::parse($validatedData['end_time']);

        // Controllo disponibilità del campo
        if (!$field->is_available) {
            throw ValidationException::withMessages([
                'field_id' => 'The selected field is not available for booking.',
            ]);
        }

        // NUOVO: Controllo delle sovrapposizioni di prenotazione
        $existingBooking = Booking::where('field_id', $field->id)
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($q) use ($start, $end) {
                    // La nuova prenotazione inizia durante una esistente
                    $q->where('start_time', '<', $end)
                      ->where('end_time', '>', $start);
                });
            })->exists();

        if ($existingBooking) {
            throw ValidationException::withMessages([
                'start_time' => 'The selected time slot is no longer available.',
            ]);
        }

        // Calcolo del prezzo
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

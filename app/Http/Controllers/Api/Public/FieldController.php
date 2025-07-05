<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\FieldResource;
use App\Models\Field;
use App\Models\Booking;
use Carbon\Carbon;
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

    public function show(Field $field)
    {
        return new FieldResource($field);
    }

    public function getAvailability(Request $request, Field $field)
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = Carbon::parse($validated['date'])->startOfDay();

        // Recupera tutte le prenotazioni per questo campo in questa data
        $bookingsOnDate = Booking::where('field_id', $field->id)
            ->whereDate('start_time', $date)
            ->get();

        // Definisci gli orari di apertura (es. 8:00 - 22:00) e la durata degli slot (es. 1 ora)
        $openingTime = $date->copy()->hour(8);
        $closingTime = $date->copy()->hour(22);
        $slotDurationMinutes = 60;

        $timeSlots = [];
        $currentTime = $openingTime->copy();

        // Genera tutti gli slot possibili e controlla la disponibilità
        while ($currentTime < $closingTime) {
            $slotEnd = $currentTime->copy()->addMinutes($slotDurationMinutes);
            $isAvailable = true;

            foreach ($bookingsOnDate as $booking) {
                $bookingStart = Carbon::parse($booking->start_time);
                $bookingEnd = Carbon::parse($booking->end_time);

                // Controlla se lo slot corrente si sovrappone con una prenotazione esistente
                if ($currentTime < $bookingEnd && $slotEnd > $bookingStart) {
                    $isAvailable = false;
                    break;
                }
            }

            $timeSlots[] = [
                'time' => $currentTime->format('H:i'),
                'available' => $isAvailable,
            ];

            $currentTime->addMinutes($slotDurationMinutes);
        }

        return response()->json(['data' => $timeSlots]);
    }
}

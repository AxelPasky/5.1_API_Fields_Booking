<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Field;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response; // <-- Aggiungi questo
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $bookings = $request->user()->bookings()->with('field')->latest()->paginate(10);

        return BookingResource::collection($bookings);
    }

    public function store(Request $request): BookingResource
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

        return new BookingResource($booking);
    }

    public function show(Request $request, Booking $booking): BookingResource|JsonResponse
    {
        if ($request->user()->id !== $booking->user_id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return new BookingResource($booking);
    }

    public function destroy(Request $request, Booking $booking): Response|JsonResponse // <-- Modifica qui
    {
        if ($request->user()->id !== $booking->user_id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $booking->delete();

        return response()->noContent();
    }

    public function update(Request $request, Booking $booking): BookingResource|JsonResponse
    {
        if ($request->user()->id !== $booking->user_id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // 2. Validazione
        $validatedData = $request->validate([
            'start_time' => 'sometimes|required|date|after:now',
            'end_time' => 'sometimes|required|date|after:start_time',
        ]);

        $start = Carbon::parse($validatedData['start_time'] ?? $booking->start_time);
        $end = Carbon::parse($validatedData['end_time'] ?? $booking->end_time);

        // 3. Controllo sovrapposizioni (escludendo la prenotazione corrente)
        $existingBooking = Booking::where('field_id', $booking->field_id)
            ->where('id', '!=', $booking->id) // <-- Escludi questa prenotazione dal controllo
            ->where(function ($query) use ($start, $end) {
                $query->where('start_time', '<', $end)
                      ->where('end_time', '>', $start);
            })->exists();

        if ($existingBooking) {
            throw ValidationException::withMessages([
                'start_time' => 'The selected time slot is no longer available.',
            ]);
        }

        // 4. Ricalcolo del prezzo
        $durationInHours = $start->diffInMinutes($end) / 60;
        $validatedData['total_price'] = $durationInHours * $booking->field->price_per_hour;

        // 5. Aggiornamento
        $booking->update($validatedData);

        return new BookingResource($booking);
    }

    public function calculatePrice(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'field_id' => 'required|exists:fields,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        $field = Field::findOrFail($validatedData['field_id']);
        $start = Carbon::parse($validatedData['start_time']);
        $end = Carbon::parse($validatedData['end_time']);

        $durationInHours = $start->diffInMinutes($end) / 60;
        $totalPrice = $durationInHours * $field->price_per_hour;

        return response()->json([
            'data' => [
                'total_price' => round($totalPrice, 2)
            ]
        ]);
    }
}

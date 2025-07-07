<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\FieldResource;
use App\Models\Field;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * @group Public
 * Endpoints for viewing and retrieving information about fields.
 * These endpoints are accessible to all users.
 */
class FieldController extends Controller
{
    /**
     * List fields
     *
     * Returns a list of all fields. Admins see all fields, others only available ones.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        
        if ($user->hasRole('Admin')) {
            $fields = Field::all();
        } else {
            $fields = Field::where('is_available', true)->get();
        }

       
        return FieldResource::collection($fields);
    }

    /**
     * Show field
     *
     * Returns detailed information about a single field.
     */
    public function show(Field $field)
    {
        return new FieldResource($field);
    }

    /**
     * Get field availability
     *
     * Returns available time slots for the field on the specified date.
     */
    public function getAvailability(Request $request, Field $field)
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = Carbon::parse($validated['date'])->startOfDay();

       
        $bookingsOnDate = Booking::where('field_id', $field->id)
            ->whereDate('start_time', $date)
            ->get();


        $openingTime = $date->copy()->hour(8);
        $closingTime = $date->copy()->hour(22);
        $slotDurationMinutes = 60;

        $timeSlots = [];
        $currentTime = $openingTime->copy();

        while ($currentTime < $closingTime) {
            $slotEnd = $currentTime->copy()->addMinutes($slotDurationMinutes);
            $isAvailable = true;

            foreach ($bookingsOnDate as $booking) {
                $bookingStart = Carbon::parse($booking->start_time);
                $bookingEnd = Carbon::parse($booking->end_time);

                
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

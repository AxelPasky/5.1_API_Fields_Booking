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
     * Returns a list of available time slots for a specific field and date.
     * @urlParam field integer required The ID of the field. Example: 1
     * @queryParam date string required The date to check for availability. Format: YYYY-MM-DD.
     */
    public function getAvailability(Request $request, Field $field)
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = Carbon::parse($validated['date'])->startOfDay();

       
        if ($date->isPast()) {
            return response()->json(['data' => []]); 
        }

        $bookingsOnDate = Booking::where('field_id', $field->id)
            ->whereDate('start_time', $date)
            ->where('status', '!=', 'cancelled')
            ->get();

        $openingTime = $date->copy()->hour(9);
        $closingTime = $date->copy()->hour(22);
        $slotDurationMinutes = 60; 

        $availableSlots = [];
        $currentTime = $openingTime->copy();

        while ($currentTime->copy()->addMinutes($slotDurationMinutes) <= $closingTime) {
            $slotStart = $currentTime->copy();
            $slotEnd = $currentTime->copy()->addMinutes($slotDurationMinutes);
            $isAvailable = true;

            foreach ($bookingsOnDate as $booking) {
                $bookingStart = Carbon::parse($booking->start_time);
                $bookingEnd = Carbon::parse($booking->end_time);

                if ($slotStart < $bookingEnd && $slotEnd > $bookingStart) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
     
                $availableSlots[] = $slotStart->toDateTimeString();
            }
            
           
            $currentTime->addMinutes(30);
        }

        return response()->json(['data' => $availableSlots]);
    }
}

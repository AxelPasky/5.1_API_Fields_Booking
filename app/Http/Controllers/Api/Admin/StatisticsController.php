<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Field; // <-- Aggiungi questo
use Illuminate\Http\Request;

/**
 * @group Admin
 * Endpoints for retrieving statistics and performance data for admins.
 */
class StatisticsController extends Controller
{
    /**
     * Get total revenue
     *
     * Returns the sum of all booking prices.
     */
    public function revenue()
    {
      
        $totalRevenue = Booking::sum('total_price');

        return response()->json([
            'data' => [
                'total_revenue' => round($totalRevenue, 2)
            ]
        ]);
    }

    /**
     * Get field performance
     *
     * Returns a list of fields with their booking counts.
     */
    public function fieldPerformance()
    {
        $fields = Field::withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->get();

        return response()->json(['data' => $fields]);
    }
}

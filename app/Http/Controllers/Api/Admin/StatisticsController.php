<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function revenue()
    {
        // Calcola la somma di tutti i total_price nella tabella bookings
        $totalRevenue = Booking::sum('total_price');

        return response()->json([
            'data' => [
                'total_revenue' => round($totalRevenue, 2)
            ]
        ]);
    }
}

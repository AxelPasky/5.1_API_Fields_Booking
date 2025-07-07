<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookingPolicy
{
   
    public function before(User $user, string $ability): bool|null
    {
        
        if ($user->is_admin) {
            return true;
        }
        return null;
    }



    public function viewAny(User $user): bool
    {
        return true;
    }

  
    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id;
    }


    public function create(User $user): bool
    {
        return true;
    }

   

    public function update(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id;
    }


    public function destroy(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id;
    }
}

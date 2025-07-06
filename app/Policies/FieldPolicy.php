<?php

namespace App\Policies;

use App\Models\Field;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FieldPolicy
{
    
    public function __construct()
    {

    }

    
    public function viewAny(User $user): bool
    {
        return true;
    }

    
    public function view(User $user, Field $field): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    
    public function update(User $user, Field $field): bool
    {
        return $user->is_admin;
    }

    
    public function delete(User $user, Field $field): bool
    {
        return $user->is_admin;
    }

}

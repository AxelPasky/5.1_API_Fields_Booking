<?php

namespace App\Policies;

use App\Models\Field;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FieldPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can view any models.
     * 
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * 
     */
    public function view(User $user, Field $field): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     * 
     */
    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can update the model.
     *
     */
    public function update(User $user, Field $field): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     * 
     */
    public function delete(User $user, Field $field): bool
    {
        return $user->is_admin;
    }

}

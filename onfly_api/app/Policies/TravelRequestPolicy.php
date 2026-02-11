<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\TravelRequest;
use App\Models\User;

class TravelRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TravelRequest $travelRequest): bool
    {
        return $user->role === UserRole::Admin || $travelRequest->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TravelRequest $travelRequest): bool
    {
        return $user->role === UserRole::Admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TravelRequest $travelRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TravelRequest $travelRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TravelRequest $travelRequest): bool
    {
        return false;
    }
}

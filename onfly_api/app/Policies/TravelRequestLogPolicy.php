<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class TravelRequestLogPolicy
{
    /**
     * Only admins can view logs.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }
}

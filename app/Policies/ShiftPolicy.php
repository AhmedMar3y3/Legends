<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Shift;

class ShiftPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->role === 'manager';
    }

    public function update(User $user, Shift $shift)
    {
        return $user->role === 'manager';
    }

    public function delete(User $user, Shift $shift)
    {
        return $user->role === 'manager';
    }
    public function viewEmployees(User $user)
{
    return $user->role === 'manager';
}
}

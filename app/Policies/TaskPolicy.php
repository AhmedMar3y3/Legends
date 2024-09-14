<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Task;

class TaskPolicy
{
    public function create(User $user)
    {
        return $user->role === 'manager';
    }

    public function update(User $user, Task $task)
    {
        return $user->role === 'manager';
    }

    public function delete(User $user, Task $task)
    {
        return $user->role === 'manager';
    }
}

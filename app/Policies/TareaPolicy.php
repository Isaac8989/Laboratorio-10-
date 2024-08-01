<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class TareaPolicy
{
    public function edit(User $user, Task $post): bool
    {
        return $post->user_id->is($user);
    }

    use HandlesAuthorization;

    public function update(User $user, Task $task)
    {
        return $user->id === $task->user_id;
    }

    public function delete(User $user, Task $task)
    {
        return $user->id === $task->user_id;
    }
}

// app/Policies/TaskPolicy.php




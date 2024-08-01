<?php
// app/Repositories/TaskRepository.php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function all(): Collection
    {
        return Task::select('id', 'name')->get();
    }

    public function getByUserId(int $userId): Collection
    {
        return Task::where('user_id', $userId)->select('id', 'name')->get();
    }

    public function update(int $id, array $data): bool
    {
        $task = Task::find($id);
        if ($task) {
            return $task->update($data);
        }
        return false;
    }

    public function delete(int $id): bool
    {
        $task = Task::find($id);
        if ($task) {
            return $task->delete();
        }
        return false;
    }
}

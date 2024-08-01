<?php

// app/Repositories/TaskRepositoryInterface.php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    public function all(): Collection;
    public function getByUserId(int $userId): Collection;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool; // Nuevo método para eliminar
}

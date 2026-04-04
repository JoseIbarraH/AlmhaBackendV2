<?php

declare(strict_types=1);

namespace Src\Admin\Team\Domain\Contracts;

use Src\Admin\Team\Domain\Entity\Team;

interface TeamRepositoryContract {
    public function save(Team $team): int;
    public function findById(int $id): ?Team;
    public function getAll(int $page = 1, int $perPage = 15): array;
    public function update(Team $team): void;
    public function delete(int $id): void;
    public function updateImage(int $id, string $imageUrl): void;
}

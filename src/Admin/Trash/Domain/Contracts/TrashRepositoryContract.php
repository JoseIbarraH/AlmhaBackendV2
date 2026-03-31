<?php

declare(strict_types=1);

namespace Src\Admin\Trash\Domain\Contracts;

interface TrashRepositoryContract
{
    /**
     * @return \Src\Admin\Trash\Domain\Entity\TrashItem[]
     */
    public function getAllDeleted(): array;

    public function restore(string $type, string|int $id): void;

    public function permanentlyDelete(string $type, string|int $id): void;
}

<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Domain\Contracts;

use Src\Admin\Procedure\Domain\Entity\ProcedureCategory;

interface ProcedureCategoryRepositoryContract
{
    public function save(ProcedureCategory $category): void;

    public function findById(int $id): ?ProcedureCategory;

    public function findByCode(string $code): ?ProcedureCategory;

    public function getAll(int $page = 1, int $perPage = 15): array;

    public function update(ProcedureCategory $category): void;

    public function delete(int $id): void;
}

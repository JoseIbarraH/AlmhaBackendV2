<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Domain\Contracts;

use Src\Admin\Procedure\Domain\Entity\ProcedureCategory;

interface ProcedureCategoryRepositoryContract
{
    public function save(ProcedureCategory $category): void;

    public function findById(int $id): ?ProcedureCategory;

    public function findByCode(string $code): ?ProcedureCategory;

    public function getAll(): array;
}

<?php

declare(strict_types=1);

namespace Src\Admin\Audit\Domain\Contracts;

use Src\Admin\Audit\Domain\Audit;

interface AuditRepositoryContract
{
    public function save(Audit $audit): void;
    public function getAll(): array;
}

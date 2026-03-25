<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Domain\Contracts;

use Src\Admin\Blog\Domain\Entity\Blog;

interface BlogRepositoryContract
{
    public function save(Blog $blog): void;

    public function findById(int $id): ?Blog;

    public function update(Blog $blog): void;

    public function delete(int $id): void;

    public function getAll(): array;
}

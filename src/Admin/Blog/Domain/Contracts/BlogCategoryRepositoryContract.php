<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Domain\Contracts;

use Src\Admin\Blog\Domain\Entity\BlogCategory;

interface BlogCategoryRepositoryContract
{
    public function save(BlogCategory $category): void;

    public function findById(int $id): ?BlogCategory;

    public function findByCode(string $code): ?BlogCategory;

    public function getAll(int $page = 1, int $perPage = 15): array;

    public function update(BlogCategory $category): void;

    public function delete(int $id): void;
}

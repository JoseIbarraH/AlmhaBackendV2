<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Domain\Contracts;
 
use Src\Admin\Procedure\Domain\Entity\Procedure;


interface ProcedureRepositoryContract
{
    public function save(Procedure $procedure): int;

    public function findById(int $id, ?string $lang = null): ?Procedure;

    public function findBySlug(string $slug, string $lang): ?Procedure;

    public function update(Procedure $procedure): void;

    public function updateImage(int $id, string $imagePath): void;

    public function delete(int $id): void;

    public function getAll(int $page = 1, int $perPage = 15, ?string $search = null, ?string $status = null): array;

    public function getAllByLang(string $lang, int $page = 1, int $perPage = 15, ?string $search = null, ?string $status = null): array;
}

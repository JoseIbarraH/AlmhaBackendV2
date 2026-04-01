<?php

namespace Src\Admin\Design\Domain;

interface DesignRepositoryContract
{
    /**
     * @return Design[]
     */
    public function findAll(): array;

    public function findByKey(string $key): ?Design;

    public function findById(int $id): ?Design;

    public function findItemById(int $itemId): ?DesignItem;

    public function updateDesignMode(int $designId, string $displayMode): void;

    public function saveItem(array $data): ?DesignItem;

    public function updateItem(int $itemId, array $data): ?DesignItem;

    public function deleteItem(int $itemId): void;
}

<?php

namespace Src\Admin\Design\Domain;

interface DesignRepositoryContract
{
    /**
     * @return Design[]
     */
    public function findAll(?string $lang = null): array;

    public function findByKey(string $key): ?Design;

    public function findById(int $id): ?Design;

    public function findItemById(int $itemId, ?string $lang = null): ?DesignItem;

    public function updateDesignMode(int $designId, string $displayMode): void;

    public function updateDesignStatus(int $designId, string $status): void;

    public function saveItem(array $data, ?string $lang = null): ?DesignItem;

    public function updateItem(int $itemId, array $data, ?string $lang = null): ?DesignItem;

    public function deleteItem(int $itemId): void;
}

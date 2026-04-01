<?php

namespace Src\Admin\Design\Domain;

class Design
{
    /**
     * @param DesignItem[] $items
     */
    public function __construct(
        public readonly ?int $id,
        public readonly string $key,
        public readonly string $displayMode,
        public readonly string $status,
        public readonly array $items = []
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'display_mode' => $this->displayMode,
            'status' => $this->status,
            'items' => array_map(fn($item) => $item->toArray(), $this->items)
        ];
    }
}

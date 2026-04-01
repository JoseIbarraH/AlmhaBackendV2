<?php

namespace Src\Admin\Design\Domain;

class DesignItem
{
    /**
     * @param DesignTranslation[] $translations
     */
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $designId,
        public readonly string $mediaType,
        public readonly ?string $mediaPath,
        public readonly int $order,
        public readonly string $status,
        public readonly array $translations = []
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'design_id' => $this->designId,
            'media_type' => $this->mediaType,
            'media_path' => $this->mediaPath,
            'url' => $this->mediaPath ? url('storage/' . $this->mediaPath) : null,
            'order' => $this->order,
            'status' => $this->status,
            'translations' => array_map(fn($t) => $t->toArray(), $this->translations)
        ];
    }
}

<?php

namespace Src\Admin\Design\Domain;

class DesignTranslation
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $lang,
        public readonly ?string $title,
        public readonly ?string $subtitle
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'lang' => $this->lang,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
        ];
    }
}

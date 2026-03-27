<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Domain\Entity;

final class ProcedureTranslation implements \JsonSerializable
{
    private ?int $id;
    private string $lang;
    private ?string $slug;
    private string $title;
    private ?string $subtitle;

    public function __construct(
        ?int $id = null,
        string $lang,
        ?string $slug = null,
        string $title,
        ?string $subtitle = null
    )
    {
        $this->id = $id;
        $this->lang = $lang;
        $this->slug = $slug;
        $this->title = $title;
        $this->subtitle = $subtitle;
    }

    public function id(): ?int { return $this->id; }
    public function lang(): string { return $this->lang; }
    public function slug(): ?string { return $this->slug; }
    public function title(): string { return $this->title; }
    public function subtitle(): string { return $this->subtitle; }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'lang' => $this->lang,
            'slug' => $this->slug,
            'title' => $this->title,
            'subtitle' => $this->subtitle
        ];
    }
}

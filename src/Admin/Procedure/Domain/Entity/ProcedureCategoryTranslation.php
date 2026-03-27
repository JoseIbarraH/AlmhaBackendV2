<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Domain\Entity;

final class ProcedureCategoryTranslation implements \JsonSerializable
{
    private ?int $id;
    private string $lang;
    private string $title;

    public function __construct(string $lang, string $title, ?int $id = null)
    {
        $this->lang = $lang;
        $this->title = $title;
        $this->id = $id;
    }

    public function lang(): string { return $this->lang; }
    public function title(): string { return $this->title; }
    public function id(): ?int { return $this->id; }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'lang' => $this->lang,
            'title' => $this->title
        ];
    }
}

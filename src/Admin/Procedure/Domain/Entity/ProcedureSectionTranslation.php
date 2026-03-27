<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Domain\Entity;

final class ProcedureSectionTranslation implements \JsonSerializable
{
    private ?int $id;
    private string $lang;
    private ?string $title;
    private ?string $contentOne;
    private ?string $contentTwo;

    public function __construct(
        ?int $id = null,
        string $lang,
        ?string $title = null,
        ?string $contentOne = null,
        ?string $contentTwo = null
    ){
        $this->id = $id;
        $this->lang = $lang;
        $this->title = $title;
        $this->contentOne = $contentOne;
        $this->contentTwo = $contentTwo;
    }

    public function id(): ?int { return $this->id; }
    public function lang(): string { return $this->lang; }
    public function title(): ?string { return $this->title; }
    public function contentOne(): ?string { return $this->contentOne; }
    public function contentTwo(): ?string { return $this->contentTwo; }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'lang' => $this->lang,
            'title' => $this->title,
            'contentOne' => $this->contentOne,
            'contentTwo' => $this->contentTwo
        ];
    }
}

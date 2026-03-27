<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Domain\Entity;

final class ProcedurePreparationStepTranslation implements \JsonSerializable
{
    private ?int $id;
    private string $lang;
    private string $title;
    private ?string $description;

    public function __construct(
        ?int $id = null,
        string $lang,
        string $title,
        ?string $description = null
    ){
        $this->id = $id;
        $this->lang = $lang;
        $this->title = $title;
        $this->description = $description;
    }

    public function id(): ?int { return $this->id; }
    public function lang(): string { return $this->lang; }
    public function title(): string { return $this->title; }
    public function description(): ?string { return $this->description; }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'lang' => $this->lang,
            'title' => $this->title,
            'description' => $this->description
        ];
    }
}

<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Domain\Entity;

final class ProcedureRecoveryPhaseTranslation implements \JsonSerializable
{
    private ?int $id;
    private string $lang;
    private ?string $period;
    private string $title;
    private ?string $description;

    public function __construct(
        ?int $id = null,
        string $lang,
        ?string $period = null,
        string $title,
        ?string $description = null
    ){
        $this->id = $id;
        $this->lang = $lang;
        $this->period = $period;
        $this->title = $title;
        $this->description = $description;
    }

    public function id(): ?int { return $this->id; }
    public function lang(): string { return $this->lang; }
    public function period(): ?string { return $this->period; }
    public function title(): string { return $this->title; }
    public function description(): ?string { return $this->description; }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'lang' => $this->lang,
            'period' => $this->period,
            'title' => $this->title,
            'description' => $this->description
        ];
    }
}

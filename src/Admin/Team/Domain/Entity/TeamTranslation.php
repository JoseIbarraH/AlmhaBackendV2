<?php

declare(strict_types=1);

namespace Src\Admin\Team\Domain\Entity;

final class TeamTranslation implements \JsonSerializable
{
    private ?int $id;
    private string $lang;
    private ?string $specialization;
    private ?string $description;
    private ?string $biography;

    public function __construct(
        string $lang,
        ?string $specialization = null,
        ?string $description = null,
        ?string $biography = null,
        ?int $id = null
    ) {
        $this->lang = $lang;
        $this->specialization = $specialization;
        $this->description = $description;
        $this->biography = $biography;
        $this->id = $id;
    }

    public function id(): ?int { return $this->id; }
    public function lang(): string { return $this->lang; }
    public function specialization(): ?string { return $this->specialization; }
    public function description(): ?string { return $this->description; }
    public function biography(): ?string { return $this->biography; }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'lang' => $this->lang,
            'specialization' => $this->specialization,
            'description' => $this->description,
            'biography' => $this->biography,
        ];
    }
}

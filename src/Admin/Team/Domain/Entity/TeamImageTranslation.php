<?php

declare(strict_types=1);

namespace Src\Admin\Team\Domain\Entity;

final class TeamImageTranslation implements \JsonSerializable
{
    private ?int $id;
    private string $lang;
    private ?string $description;

    public function __construct(
        string $lang,
        ?string $description = null,
        ?int $id = null
    ) {
        $this->lang = $lang;
        $this->description = $description;
        $this->id = $id;
    }

    public function id(): ?int { return $this->id; }
    public function lang(): string { return $this->lang; }
    public function description(): ?string { return $this->description; }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'lang' => $this->lang,
            'description' => $this->description,
        ];
    }
}

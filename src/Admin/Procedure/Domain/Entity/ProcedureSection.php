<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Domain\Entity;

final class ProcedureSection implements \JsonSerializable
{
    private ?int $id;
    private string $type;
    private ?string $image;
    private int $order;

    /** @var ProcedureSectionTranslation[] */
    private array $translations;

    public function __construct(
        ?int $id = null,
        string $type,
        ?string $image = null,
        int $order = 0,
        array $translations = []
    ){
        $this->id = $id;
        $this->type = $type;
        $this->image = $image;
        $this->order = $order;
        $this->translations = $translations;
    }

    public function id(): ?int { return $this->id; }
    public function type(): string { return $this->type; }
    public function image(): ?string { return $this->image; }
    public function order(): int { return $this->order; }

    /** @return ProcedureSectionTranslation[] */
    public function translations(): array { return $this->translations; }

    public function addTranslation(ProcedureSectionTranslation $translation): void
    {
        $this->translations[] = $translation;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'image' => $this->image,
            'order' => $this->order,
            'translations' => $this->translations,
        ];
    }
}

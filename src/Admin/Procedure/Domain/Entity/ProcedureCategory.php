<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Domain\Entity;

final class ProcedureCategory implements \JsonSerializable
{
    private ?int $id;
    private string $code;
    private ?string $name;

    /** @var ProcedureCategoryTranslation[] */
    private array $translations;

    public function __construct(string $code, array $translations = [], ?int $id = null, ?string $name = null)
    {
        $this->id = $id;
        $this->code = $code;
        $this->translations = $translations;
        $this->name = $name;
    }

    public function id(): ?int { return $this->id; }
    public function code(): string { return $this->code; }

    /** @return ProcedureCategoryTranslation[] */
    public function translations(): array { return $this->translations; }

    public function addTranslation(ProcedureCategoryTranslation $translation): void
    {
        $this->translations[] = $translation;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name ?? (count($this->translations) > 0 ? $this->translations[0]->title() : null),
            'translations' => $this->translations
        ];
    }
}


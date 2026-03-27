<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Domain\Entity;

final class ProcedureCategory implements \JsonSerializable
{
    private ?int $id;
    private string $code;

    /** @var ProcedureCategoryTranslation[] */
    private array $translations;

    public function __construct(string $code, array $translations = [], ?int $id = null)
    {
        $this->id = $id;
        $this->code = $code;
        $this->translations = $translations;
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
            'translations' => $this->translations
        ];
    }
}


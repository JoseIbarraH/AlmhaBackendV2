<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Domain\Entity;

final class ProcedureRecoveryPhase implements \JsonSerializable
{
    private ?int $id;
    private int $order;

    /** @var ProcedureRecoveryPhaseTranslation[] */
    private array $translations;

    public function __construct(
        ?int $id = null,
        int $order,
        array $translations = []
    ){
        $this->id = $id;
        $this->order = $order;
        $this->translations = $translations;
    }

    public function id(): ?int { return $this->id; }
    public function order(): int { return $this->order; }

    /** @return ProcedureRecoveryPhaseTranslation[] */
    public function translations(): array { return $this->translations; }

    public function addTranslation(ProcedureRecoveryPhaseTranslation $translation): void
    {
        $this->translations[] = $translation;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'order' => $this->order,
            'translations' => $this->translations,
        ];
    }
}

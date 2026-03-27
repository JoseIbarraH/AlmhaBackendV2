<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Domain\Entity;

use RuntimeException;

final class ProcedurePostoperativeInstruction implements \JsonSerializable
{
    private ?int $id;
    private string $type;
    private int $order;

    /** @var ProcedurePostoperativeInstructionTranslation[] */
    private array $translations;

    public function __construct(
        ?int $id = null,
        string $type,
        int $order,
        array $translations = []
    ) {
        if (!in_array($type, ['do', 'dont'])) {
            throw new RuntimeException("Invalid postoperative instruction type: $type");
        }

        $this->id = $id;
        $this->type = $type;
        $this->order = $order;
        $this->translations = $translations;
    }

    public function id(): ?int { return $this->id; }
    public function type(): string { return $this->type; }
    public function order(): int { return $this->order; }

    /**
     * Summary of translations
     * @return ProcedurePostoperativeInstructionTranslation[]
     */
    public function translations(): array { return $this->translations; }

    public function addTranslation(ProcedurePostoperativeInstructionTranslation $translation): void
    {
        $this->translations[] = $translation;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'order' => $this->order,
            'translations' => $this->translations,
        ];
    }
}

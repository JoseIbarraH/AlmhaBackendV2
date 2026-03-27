<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Domain\Entity;

use RuntimeException;

final class ProcedureResultGallery implements \JsonSerializable
{
    private ?int $id;
    private string $path;
    private string $type;
    private ?int $pairId;
    private int $order;

    public function __construct(
        ?int $id = null,
        string $path,
        string $type,
        ?int $pairId = null,
        int $order = 0
    ){
        if (!in_array($type, ['before', 'after'])) {
            throw new RuntimeException("Invalid procedure result type: $type");
        }

        $this->id = $id;
        $this->path = $path;
        $this->type = $type;
        $this->pairId = $pairId;
        $this->order = $order;
    }

    public function id(): ?int { return $this->id; }
    public function path(): string { return $this->path; }
    public function type(): string { return $this->type; }
    public function pairId(): ?int { return $this->pairId; }
    public function order(): int { return $this->order; }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'path' => $this->path,
            'type' => $this->type,
            'pairId' => $this->pairId,
            'order' => $this->order
        ];
    }
}

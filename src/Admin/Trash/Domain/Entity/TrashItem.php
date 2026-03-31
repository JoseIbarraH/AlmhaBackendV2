<?php

declare(strict_types=1);

namespace Src\Admin\Trash\Domain\Entity;

use DateTime;

final class TrashItem implements \JsonSerializable
{
    private string|int $id;
    private string $type;
    private string $name;
    private ?DateTime $deletedAt;

    public function __construct(
        string|int $id,
        string $type,
        string $name,
        ?DateTime $deletedAt = null
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->name = $name;
        $this->deletedAt = $deletedAt;
    }

    public function id(): string|int { return $this->id; }
    public function type(): string { return $this->type; }
    public function name(): string { return $this->name; }
    public function deletedAt(): ?DateTime { return $this->deletedAt; }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'deletedAt' => $this->deletedAt ? $this->deletedAt->format(DateTime::ATOM) : null,
        ];
    }
}

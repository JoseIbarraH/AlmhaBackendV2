<?php

namespace Src\Admin\Settings\Domain;

class Setting
{
    public function __construct(
        private ?int $id,
        private string $key,
        private mixed $value,
        private string $group
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function group(): string
    {
        return $this->group;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'value' => $this->value,
            'group' => $this->group,
        ];
    }
}

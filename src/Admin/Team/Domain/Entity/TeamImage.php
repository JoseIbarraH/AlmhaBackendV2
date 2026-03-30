<?php

declare(strict_types=1);

namespace Src\Admin\Team\Domain\Entity;

final class TeamImage implements \JsonSerializable
{
    private ?int $id;
    private string $path;
    private int $order;

    /** @var TeamImageTranslation[] */
    private array $translations;

    public function __construct(
        string $path,
        int $order = 0,
        array $translations = [],
        ?int $id = null
    ) {
        $this->path = $path;
        $this->order = $order;
        $this->translations = $translations;
        $this->id = $id;
    }

    public function id(): ?int { return $this->id; }
    public function path(): string { return $this->path; }
    public function order(): int { return $this->order; }

    /** @return TeamImageTranslation[] */
    public function translations(): array { return $this->translations; }

    public function getTranslation(string $lang): ?TeamImageTranslation
    {
        foreach ($this->translations as $translation) {
            if ($translation->lang() === $lang) {
                return $translation;
            }
        }
        return null;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'path' => $this->path,
            'order' => $this->order,
            'translations' => $this->translations,
        ];
    }
}

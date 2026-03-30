<?php

declare(strict_types=1);

namespace Src\Admin\Team\Domain\Entity;

use RuntimeException;

final class Team implements \JsonSerializable
{
    private ?int $id;
    private ?string $userId;
    private ?string $slug;
    private string $name;
    private string $status;
    private ?string $image;

    /** @var TeamTranslation[] */
    private array $translations;

    /** @var TeamImage[] */
    private array $images;

    public function __construct(
        ?string $slug,
        string $name,
        string $status = 'active',
        ?string $userId = null,
        ?string $image = null,
        array $translations = [],
        array $images = [],
        ?int $id = null
    ) {
        if (!in_array($status, ['active', 'inactive'])) {
            throw new RuntimeException("Invalid team status: $status");
        }

        $this->id = $id;
        $this->userId = $userId;
        $this->slug = $slug;
        $this->name = $name;
        $this->status = $status;
        $this->image = $image;
        $this->translations = $translations;
        $this->images = $images;
    }

    // Getters
    public function id(): ?int { return $this->id; }
    public function userId(): ?string { return $this->userId; }
    public function slug(): ?string { return $this->slug; }
    public function name(): string { return $this->name; }
    public function status(): string { return $this->status; }
    public function image(): ?string { return $this->image; }

    /** @return TeamTranslation[] */
    public function translations(): array { return $this->translations; }

    /** @return TeamImage[] */
    public function images(): array { return $this->images; }

    public function changeStatus(string $newStatus): void
    {
        if (!in_array($newStatus, ['active', 'inactive'])) {
            throw new RuntimeException("Invalid team status: $newStatus");
        }
        $this->status = $newStatus;
    }

    public function getTranslation(string $lang): ?TeamTranslation
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
            'userId' => $this->userId,
            'slug' => $this->slug,
            'name' => $this->name,
            'status' => $this->status,
            'image' => $this->image,
            'translations' => $this->translations,
            'images' => $this->images,
        ];
    }
}

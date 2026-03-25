<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Domain\Entity;

use DateTime;
use RuntimeException;

final class Blog implements \JsonSerializable
{
    private ?int $id;
    private ?int $userId;
    private string $slug;
    private ?string $image;
    private string $categoryCode;
    private ?string $writer;
    private int $views;
    private string $status;
    private ?DateTime $publishedAt;
    private ?DateTime $notificationSentAt;

    /** @var BlogTranslation[] */
    private array $translations;

    public function __construct(
        string $slug,
        string $categoryCode,
        string $status = 'draft',
        ?int $userId = null,
        ?string $image = null,
        ?string $writer = null,
        int $views = 0,
        ?DateTime $publishedAt = null,
        ?DateTime $notificationSentAt = null,
        array $translations = [],
        ?int $id = null
    ) {
        if (!in_array($status, ['draft', 'published', 'archived'])) {
            throw new RuntimeException("Invalid blog status: $status");
        }

        $this->slug = $slug;
        $this->categoryCode = $categoryCode;
        $this->status = $status;
        $this->userId = $userId;
        $this->image = $image;
        $this->writer = $writer;
        $this->views = $views;
        $this->publishedAt = $publishedAt;
        $this->notificationSentAt = $notificationSentAt;
        $this->translations = $translations;
        $this->id = $id;
    }

    // Getters for Aggregate Root
    public function id(): ?int { return $this->id; }
    public function userId(): ?int { return $this->userId; }
    public function slug(): string { return $this->slug; }
    public function image(): ?string { return $this->image; }
    public function categoryCode(): string { return $this->categoryCode; }
    public function writer(): ?string { return $this->writer; }
    public function views(): int { return $this->views; }
    public function status(): string { return $this->status; }
    public function publishedAt(): ?DateTime { return $this->publishedAt; }
    public function notificationSentAt(): ?DateTime { return $this->notificationSentAt; }

    /** @return BlogTranslation[] */
    public function translations(): array { return $this->translations; }

    public function changeStatus(string $newStatus): void
    {
        if (!in_array($newStatus, ['draft', 'published', 'archived'])) {
            throw new RuntimeException("Invalid blog status: $newStatus");
        }
        $this->status = $newStatus;
        if ($newStatus === 'published' && !$this->publishedAt) {
            $this->publishedAt = new DateTime();
        }
    }

    public function addTranslation(BlogTranslation $translation): void
    {
        $this->translations[] = $translation;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'slug' => $this->slug,
            'image' => $this->image,
            'categoryCode' => $this->categoryCode,
            'writer' => $this->writer,
            'views' => $this->views,
            'status' => $this->status,
            'publishedAt' => $this->publishedAt ? $this->publishedAt->format(DateTime::ATOM) : null,
            'notificationSentAt' => $this->notificationSentAt ? $this->notificationSentAt->format(DateTime::ATOM) : null,
            'translations' => $this->translations,
        ];
    }
}

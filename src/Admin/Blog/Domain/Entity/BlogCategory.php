<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Domain\Entity;

final class BlogCategory implements \JsonSerializable
{
    private ?int $id;
    private string $code;
    private ?string $name;
    
    /** @var BlogCategoryTranslation[] */
    private array $translations;

    public function __construct(string $code, array $translations = [], ?int $id = null, ?string $name = null)
    {
        $this->code = $code;
        $this->translations = $translations;
        $this->id = $id;
        $this->name = $name;
    }

    public function id(): ?int { return $this->id; }
    public function code(): string { return $this->code; }
    
    /** @return BlogCategoryTranslation[] */
    public function translations(): array { return $this->translations; }

    public function addTranslation(BlogCategoryTranslation $translation): void
    {
        $this->translations[] = $translation;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name ?? (count($this->translations) > 0 ? $this->translations[0]->title() : null),
            'translations' => $this->translations,
        ];
    }
}

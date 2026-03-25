<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Domain\Entity;

final class BlogTranslation implements \JsonSerializable
{
    private ?int $id;
    private string $lang;
    private string $title;
    private ?string $content;

    public function __construct(string $lang, string $title, ?string $content = null, ?int $id = null)
    {
        $this->lang = $lang;
        $this->title = $title;
        $this->content = $content;
        $this->id = $id;
    }

    public function lang(): string { return $this->lang; }
    public function title(): string { return $this->title; }
    public function content(): ?string { return $this->content; }
    public function id(): ?int { return $this->id; }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'lang' => $this->lang,
            'title' => $this->title,
            'content' => $this->content,
        ];
    }
}

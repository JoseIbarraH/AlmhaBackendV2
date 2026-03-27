<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Domain\Entity;

final class ProcedurePostoperativeInstructionTranslation implements \JsonSerializable
{
    private ?int $id;
    private string $lang;
    private string $content;

    public function __construct(
        ?int $id = null,
        string $lang,
        string $content
    ){
        $this->id = $id;
        $this->lang = $lang;
        $this->content = $content;
    }

    public function id(): ?int { return $this->id; }
    public function lang(): string { return $this->lang; }
    public function content(): string { return $this->content; }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'lang' => $this->lang,
            'content' => $this->content,
        ];
    }
}

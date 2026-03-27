<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Domain\Entity;

final class ProcedureFaqTranslation implements \JsonSerializable
{
    private ?int $id;
    private string $lang;
    private string $question;
    private string $answer;

    public function __construct(
        ?int $id = null,
        string $lang,
        string $question,
        string $answer
    ){
        $this->id = $id;
        $this->lang = $lang;
        $this->question = $question;
        $this->answer = $answer;
    }

    public function id(): ?int { return $this->id; }
    public function lang(): string { return $this->lang; }
    public function question(): string { return $this->question; }
    public function answer(): string { return $this->answer; }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'lang' => $this->lang,
            'question' => $this->question,
            'answer' => $this->answer
        ];
    }
}

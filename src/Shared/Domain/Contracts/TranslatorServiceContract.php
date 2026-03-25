<?php

namespace Src\Shared\Domain\Contracts;

interface TranslatorServiceContract
{
    /**
     * Translates the given text(s) into the target language.
     *
     * @param string|array $text The text(s) to be translated.
     * @param string $targetLanguage The ISO 639-1 code of the target language (e.g., 'en', 'es', 'pt').
     * @param string|null $sourceLanguage Optional source language code.
     * @return string|array The translated text(s).
     */
    public function translate(string|array $text, string $targetLanguage, ?string $sourceLanguage = null): string|array;
}

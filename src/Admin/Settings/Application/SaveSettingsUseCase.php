<?php

namespace Src\Admin\Settings\Application;

use Illuminate\Support\Facades\Log;
use Src\Admin\Settings\Domain\SettingRepositoryContract;
use Src\Shared\Domain\Contracts\TranslatorServiceContract;
use Src\Shared\Infrastructure\Cache\ClientCache;

class SaveSettingsUseCase
{
    private const ABOUT_KEYS = ['about_mission', 'about_vision'];

    public function __construct(
        private SettingRepositoryContract $repository
    ) {}

    public function execute(array $settings, string $group): void
    {
        if ($group === 'general') {
            $settings = $this->processAboutTranslations($settings);
        }

        $this->repository->saveMany($settings, $group);

        if ($group === 'general') {
            ClientCache::flushGroups('about', 'contact_data');
        }
    }

    private function processAboutTranslations(array $settings): array
    {
        foreach (self::ABOUT_KEYS as $key) {
            if (!\array_key_exists($key, $settings)) {
                continue;
            }
            $settings[$key] = $this->ensureAllLanguages($settings[$key]);
        }
        return $settings;
    }

    /**
     * Admin sends { lang: 'es'|'en', title, description } — the language the editor is on.
     * Backend translates that source into ALL THREE languages so they stay in sync.
     *
     * Legacy shape { es: {...}, en: {...} } is still accepted for backward compatibility.
     * If the translator is unavailable, the other languages fall back to the source text.
     */
    private function ensureAllLanguages(mixed $value): array
    {
        $value = \is_array($value) ? $value : [];

        $sourceLang = $value['lang'] ?? null;
        if (\is_string($sourceLang) && \in_array($sourceLang, ['es', 'en', 'fr'], true)) {
            $source = $this->normalizeLangPayload($value);
            return $this->generateAllLanguages($source, $sourceLang);
        }

        // Legacy shape — pick whichever language has content as the source.
        $es = $this->normalizeLangPayload($value['es'] ?? null);
        $en = $this->normalizeLangPayload($value['en'] ?? null);

        [$source, $legacySourceLang] = match (true) {
            !$this->isEmpty($es) => [$es, 'es'],
            !$this->isEmpty($en) => [$en, 'en'],
            default              => [null, null],
        };

        if ($source === null) {
            $empty = $this->normalizeLangPayload(null);
            return ['es' => $empty, 'en' => $empty, 'fr' => $empty];
        }

        return $this->generateAllLanguages($source, $legacySourceLang);
    }

    /**
     * Generate the {es, en, fr} triple from a single source. The source language is copied
     * verbatim; the other two are produced by the translator (or copied if it's unavailable).
     */
    private function generateAllLanguages(array $source, string $sourceLang): array
    {
        $allLangs = ['es', 'en', 'fr'];

        if ($this->isEmpty($source)) {
            $empty = $this->normalizeLangPayload(null);
            return ['es' => $empty, 'en' => $empty, 'fr' => $empty];
        }

        $translator = $this->resolveTranslator();
        $result = [];

        foreach ($allLangs as $lang) {
            if ($lang === $sourceLang) {
                $result[$lang] = $source;
                continue;
            }
            $result[$lang] = $translator !== null
                ? $this->translatePayload($translator, $source, $lang, $sourceLang)
                : $source;
        }

        return $result;
    }

    private function resolveTranslator(): ?TranslatorServiceContract
    {
        try {
            return app(TranslatorServiceContract::class);
        } catch (\Throwable $e) {
            Log::warning('[Settings.About] Translator unavailable, fr will fall back to source language: ' . $e->getMessage());
            return null;
        }
    }

    private function normalizeLangPayload(mixed $payload): array
    {
        if (!\is_array($payload)) {
            return ['title' => '', 'description' => ''];
        }
        return [
            'title'       => isset($payload['title']) ? trim((string) $payload['title']) : '',
            'description' => isset($payload['description']) ? trim((string) $payload['description']) : '',
        ];
    }

    private function isEmpty(array $payload): bool
    {
        return ($payload['title'] ?? '') === '' && ($payload['description'] ?? '') === '';
    }

    private function translatePayload(TranslatorServiceContract $translator, array $source, string $target, string $sourceLang): array
    {
        try {
            return [
                'title'       => $source['title']       !== '' ? $this->decodeEntities((string) $translator->translate($source['title'], $target, $sourceLang))       : '',
                'description' => $source['description'] !== '' ? $this->decodeEntities((string) $translator->translate($source['description'], $target, $sourceLang)) : '',
            ];
        } catch (\Throwable $e) {
            Log::warning('[Settings.About] Translation failed, falling back to source: ' . $e->getMessage());
            return $source;
        }
    }

    /**
     * Google Translate returns text with HTML entities (e.g. "C&#39;est"). Decode them before storing.
     */
    private function decodeEntities(string $text): string
    {
        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

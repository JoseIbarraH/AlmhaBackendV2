<?php

namespace Src\Admin\Design\Application;

use Illuminate\Support\Facades\Storage;
use Src\Admin\Design\Domain\DesignRepositoryContract;
use Src\Shared\Domain\Contracts\TranslatorServiceContract;

class UpdateDesignItemUseCase
{
    private DesignRepositoryContract $repository;
    private TranslatorServiceContract $translator;

    public function __construct(DesignRepositoryContract $repository, TranslatorServiceContract $translator)
    {
        $this->repository = $repository;
        $this->translator = $translator;
    }

    public function execute(int $itemId, array $data, string $baseLang, array $targetLanguages = [])
    {
        $item = $this->repository->findItemById($itemId, $baseLang);
        if (!$item) {
            throw new \Exception("Item not found");
        }

        if (isset($data['media_file'])) {
            // Remove old (handle both full URLs and relative paths)
            if ($item->mediaPath) {
                $oldPath = \Src\Shared\Infrastructure\Support\MediaUrl::toRelativePath($item->mediaPath);
                if ($oldPath !== '') {
                    Storage::disk('s3')->delete($oldPath);
                }
            }

            $path = $data['media_file']->store('designs', 's3');
            $data['media_path'] = $path;
            
            // Auto detect media type if not provided explicitly
            if (!isset($data['media_type'])) {
                $mimeType = $data['media_file']->getMimeType();
                $data['media_type'] = str_starts_with($mimeType, 'video/') ? 'video' : 'image';
            }
        }

        // --- Handle Translations ---
        $title = $data['title'] ?? null;
        $subtitle = $data['subtitle'] ?? null;
        $translationsData = $data['translations'] ?? [];

        // If translations array is provided, try to find the one matching baseLang
        if (!empty($translationsData) && is_array($translationsData)) {
            $baseTranslation = null;
            foreach ($translationsData as $t) {
                if (($t['lang'] ?? '') === $baseLang) {
                    $baseTranslation = $t;
                    break;
                }
            }
            
            // Fallback to first one if not found specifically for baseLang
            if (!$baseTranslation) {
                $baseTranslation = $translationsData[0];
            }

            $title = $baseTranslation['title'] ?? $title;
            $subtitle = $baseTranslation['subtitle'] ?? $subtitle;
        }

        // Only proceed if we have content to translate or explicitly sent translations
        if ($title !== null || $subtitle !== null || !empty($translationsData)) {
            $translations = [];
            
            // Original translation
            $translations[] = [
                'lang' => $baseLang,
                'title' => $title,
                'subtitle' => $subtitle
            ];

            // Auto-translate to target languages
            foreach ($targetLanguages as $lang) {
                $translatedTitle = $title ? $this->translator->translate($title, $lang, $baseLang) : null;
                $translatedSubtitle = $subtitle ? $this->translator->translate($subtitle, $lang, $baseLang) : null;
                
                $translations[] = [
                    'lang' => $lang,
                    'title' => $translatedTitle,
                    'subtitle' => $translatedSubtitle
                ];
            }

            $data['translations'] = $translations;
        }

        $updatedItem = $this->repository->updateItem($itemId, $data, $baseLang);
        return $updatedItem ? $updatedItem->toArray() : null;
    }
}

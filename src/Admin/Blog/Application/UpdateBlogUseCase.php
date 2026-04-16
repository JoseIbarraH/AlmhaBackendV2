<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Application;

use Src\Admin\Blog\Domain\Contracts\BlogRepositoryContract;
use Src\Admin\Blog\Domain\Entity\Blog;
use Src\Admin\Blog\Domain\Entity\BlogTranslation;
use Src\Shared\Domain\Contracts\TranslatorServiceContract;
use RuntimeException;

final class UpdateBlogUseCase
{
    private BlogRepositoryContract $repository;
    private TranslatorServiceContract $translator;

    public function __construct(BlogRepositoryContract $repository, TranslatorServiceContract $translator)
    {
        $this->repository = $repository;
        $this->translator = $translator;
    }

    /**
     * Patch-style update: only non-null params are updated.
     * Null means "not sent / keep existing value".
     */
    public function execute(
        int $id,
        string $baseLang,
        ?string $categoryCode = null,
        ?string $title = null,
        ?string $content = null,
        array $targetLanguages = [],
        ?int $userId = null,
        ?string $image = null,
        ?string $writer = null,
        bool $contentWasSent = false
    ): void
    {
        $blog = $this->repository->findById($id);

        if (!$blog) {
            throw new RuntimeException("Blog no encontrado con el ID: $id");
        }

        // Resolve values: use sent value or fallback to existing
        $finalCategoryCode = $categoryCode ?? $blog->categoryCode();
        $finalUserId = $userId ?? $blog->userId();
        $finalWriter = $writer ?? $blog->writer();

        // Determine if translatable content changed
        $titleChanged = $title !== null;
        $contentChanged = $contentWasSent;

        // Find existing baseLang translation (or first available)
        $existingTranslation = $blog->getTranslation($baseLang);
        if (!$existingTranslation && count($blog->translations()) > 0) {
            $existingTranslation = $blog->translations()[0];
        }

        $finalTitle = $title ?? ($existingTranslation ? $existingTranslation->title() : '');
        $finalContent = $contentChanged ? $content : ($existingTranslation ? $existingTranslation->content() : null);

        // Only re-translate if text content actually changed
        if ($titleChanged || $contentChanged) {
            $translations = [];
            $translations[] = new BlogTranslation($baseLang, $finalTitle, $finalContent);

            foreach ($targetLanguages as $lang) {
                $translatedTitle = $this->translator->translate($finalTitle, $lang, $baseLang);
                $translatedContent = $finalContent ? $this->translator->translate($finalContent, $lang, $baseLang) : null;

                $translations[] = new BlogTranslation($lang, $translatedTitle, $translatedContent);
            }
        } else {
            // No text changed — preserve existing translations
            $translations = $blog->translations();
        }

        $blogToUpdate = new Blog(
            $finalCategoryCode,
            $blog->status(),
            $finalUserId,
            $image ?? $blog->image(),
            $finalWriter,
            $blog->views(),
            $blog->publishedAt(),
            $blog->notificationSentAt(),
            $translations,
            $blog->id()
        );

        $this->repository->update($blogToUpdate);
    }
}

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

    public function execute(
        int $id,
        string $categoryCode,
        string $baseLang,
        string $title,
        ?string $content,
        array $targetLanguages = [],
        ?int $userId = null,
        ?string $image = null,
        ?string $writer = null
    ): void
    {
        $blog = $this->repository->findById($id);

        if (!$blog) {
            throw new RuntimeException("Blog no encontrado con el ID: $id");
        }

        $translations = [];
        $translations[] = new BlogTranslation($baseLang, $title, $content);

        foreach ($targetLanguages as $lang) {
            $translatedTitle = $this->translator->translate($title, $lang, $baseLang);
            $translatedContent = $content ? $this->translator->translate($content, $lang, $baseLang) : null;
            
            $translations[] = new BlogTranslation($lang, $translatedTitle, $translatedContent);
        }

        $blogToUpdate = new Blog(
            $categoryCode,
            $blog->status(),
            $userId ?? $blog->userId(),
            $image ?? $blog->image(),
            $writer ?? $blog->writer(),
            $blog->views(),
            $blog->publishedAt(),
            $blog->notificationSentAt(),
            $translations,
            $blog->id()
        );

        $this->repository->update($blogToUpdate);
    }
}

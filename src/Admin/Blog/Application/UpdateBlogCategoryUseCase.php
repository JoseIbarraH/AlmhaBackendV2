<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Application;

use Src\Admin\Blog\Domain\Contracts\BlogCategoryRepositoryContract;
use Src\Admin\Blog\Domain\Entity\BlogCategory;
use Src\Admin\Blog\Domain\Entity\BlogCategoryTranslation;
use Src\Shared\Domain\Contracts\TranslatorServiceContract;
use RuntimeException;

final class UpdateBlogCategoryUseCase
{
    private BlogCategoryRepositoryContract $repository;
    private TranslatorServiceContract $translator;

    public function __construct(BlogCategoryRepositoryContract $repository, TranslatorServiceContract $translator)
    {
        $this->repository = $repository;
        $this->translator = $translator;
    }

    public function execute(
        int $id,
        string $code,
        string $baseLang,
        string $title,
        array $targetLanguages = []
    ): void
    {
        $category = $this->repository->findById($id);

        if (!$category) {
            throw new RuntimeException("Category not found with ID: $id");
        }

        $translations = [];
        
        // Base translation
        $translations[] = new BlogCategoryTranslation(null, $baseLang, $title);

        // Target translations
        foreach ($targetLanguages as $lang) {
            $translatedTitle = $this->translator->translate($title, $lang, $baseLang);
            $translations[] = new BlogCategoryTranslation(null, $lang, $translatedTitle);
        }

        $updatedCategory = new BlogCategory(
            $code,
            $translations,
            $id
        );

        $this->repository->update($updatedCategory);
    }
}

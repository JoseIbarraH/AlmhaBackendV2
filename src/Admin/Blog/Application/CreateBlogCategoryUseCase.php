<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Application;

use Src\Admin\Blog\Domain\Contracts\BlogCategoryRepositoryContract;
use Src\Admin\Blog\Domain\Entity\BlogCategory;
use Src\Admin\Blog\Domain\Entity\BlogCategoryTranslation;
use Src\Shared\Domain\Contracts\TranslatorServiceContract;

final class CreateBlogCategoryUseCase
{
    private BlogCategoryRepositoryContract $repository;
    private TranslatorServiceContract $translator;

    public function __construct(BlogCategoryRepositoryContract $repository, TranslatorServiceContract $translator)
    {
        $this->repository = $repository;
        $this->translator = $translator;
    }

    public function execute(
        string $code,
        string $baseLang,
        string $title,
        array $targetLanguages = []
    ): void
    {
        $translations = [];
        $translations[] = new BlogCategoryTranslation($baseLang, $title);

        foreach ($targetLanguages as $lang) {
            $translatedTitle = $this->translator->translate($title, $lang, $baseLang);
            $translations[] = new BlogCategoryTranslation($lang, $translatedTitle);
        }

        $category = new BlogCategory($code, $translations);

        $this->repository->save($category);
    }
}

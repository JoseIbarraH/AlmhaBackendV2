<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Application;

use Src\Admin\Procedure\Domain\Contracts\ProcedureCategoryRepositoryContract;
use Src\Admin\Procedure\Domain\Entity\ProcedureCategory;
use Src\Admin\Procedure\Domain\Entity\ProcedureCategoryTranslation;
use Src\Shared\Domain\Contracts\TranslatorServiceContract;
use RuntimeException;

final class UpdateProcedureCategoryUseCase
{
    private ProcedureCategoryRepositoryContract $repository;
    private TranslatorServiceContract $translator;

    public function __construct(ProcedureCategoryRepositoryContract $repository, TranslatorServiceContract $translator)
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
        $translations[] = new ProcedureCategoryTranslation($baseLang, $title);

        // Target translations
        foreach ($targetLanguages as $lang) {
            $translatedTitle = $this->translator->translate($title, $lang, $baseLang);
            $translations[] = new ProcedureCategoryTranslation($lang, $translatedTitle);
        }

        $updatedCategory = new ProcedureCategory(
            $code,
            $translations,
            $id
        );

        $this->repository->update($updatedCategory);
    }
}

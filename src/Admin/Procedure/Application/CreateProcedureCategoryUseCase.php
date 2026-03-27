<?php

declare(strict_types=1);

use Src\Admin\Procedure\Domain\Contracts\ProcedureCategoryRepositoryContract;
use Src\Admin\Procedure\Domain\Entity\ProcedureCategory;
use Src\Admin\Procedure\Domain\Entity\ProcedureCategoryTranslation;
use Src\Shared\Domain\Contracts\TranslatorServiceContract;
final class CreateProcedureCategoryUseCase
{
    private ProcedureCategoryRepositoryContract $repository;
    private TranslatorServiceContract $translator;

    public function __construct(ProcedureCategoryRepositoryContract $repository, TranslatorServiceContract $translator)
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
        $translations[] = new ProcedureCategoryTranslation($baseLang, $title);

        foreach ($targetLanguages as $lang) {
            $translatedTitle = $this->translator->translate($title, $lang, $baseLang);
            $translations[] = new ProcedureCategoryTranslation($lang, $translatedTitle);
        }

        $category = new ProcedureCategory($code, $translations);

        $this->repository->save($category);
    }
}

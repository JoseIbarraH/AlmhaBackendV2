<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Application;

use Src\Admin\Procedure\Domain\Contracts\ProcedureRepositoryContract;
use Src\Admin\Procedure\Domain\Entity\Procedure;
use Src\Admin\Procedure\Domain\Entity\ProcedureTranslation;
use Src\Admin\Procedure\Domain\Entity\ProcedureSection;
use Src\Admin\Procedure\Domain\Entity\ProcedureSectionTranslation;
use Src\Admin\Procedure\Domain\Entity\ProcedureFaq;
use Src\Admin\Procedure\Domain\Entity\ProcedureFaqTranslation;
use Src\Admin\Procedure\Domain\Entity\ProcedurePostoperativeInstruction;
use Src\Admin\Procedure\Domain\Entity\ProcedurePostoperativeInstructionTranslation;
use Src\Admin\Procedure\Domain\Entity\ProcedurePreparationStep;
use Src\Admin\Procedure\Domain\Entity\ProcedurePreparationStepTranslation;
use Src\Admin\Procedure\Domain\Entity\ProcedureRecoveryPhase;
use Src\Admin\Procedure\Domain\Entity\ProcedureRecoveryPhaseTranslation;
use Src\Admin\Procedure\Domain\Entity\ProcedureResultGallery;
use Src\Shared\Domain\Contracts\TranslatorServiceContract;
use RuntimeException;

final class UpdateProcedureUseCase
{
    private ProcedureRepositoryContract $repository;
    private TranslatorServiceContract $translator;

    public function __construct(ProcedureRepositoryContract $repository, TranslatorServiceContract $translator)
    {
        $this->repository = $repository;
        $this->translator = $translator;
    }

    /**
     * Patch-style update: null params mean "keep existing value".
     * Arrays passed as null mean "keep existing children".
     */
    public function execute(
        int $id,
        string $baseLang,
        ?string $categoryCode = null,
        ?string $title = null,
        ?string $subtitle = null,
        array $targetLanguages = [],
        ?string $status = null,
        ?string $userId = null,
        ?string $image = null,
        ?array $sectionsData = null,
        ?array $faqsData = null,
        ?array $postoperativeInstructionsData = null,
        ?array $preparationStepsData = null,
        ?array $recoveryPhasesData = null,
        ?array $galleryData = null,
        bool $titleWasSent = false,
        bool $subtitleWasSent = false
    ): void
    {
        $procedure = $this->repository->findById($id);

        if (!$procedure) {
            throw new RuntimeException("Procedure not found with ID: $id");
        }

        // Resolve scalar values: use sent value or fallback to existing
        $finalCategoryCode = $categoryCode ?? $procedure->categoryCode();
        $finalStatus = $status ?? $procedure->status();
        $finalUserId = $userId ?? $procedure->userId();

        // Determine if translatable content changed
        $textChanged = $titleWasSent || $subtitleWasSent;

        // Find existing baseLang translation
        $existingTranslation = $procedure->getTranslation($baseLang);
        if (!$existingTranslation && count($procedure->translations()) > 0) {
            $existingTranslation = $procedure->translations()[0];
        }

        $finalTitle = $titleWasSent ? ($title ?? '') : ($existingTranslation ? $existingTranslation->title() : '');
        $finalSubtitle = $subtitleWasSent ? $subtitle : ($existingTranslation ? $existingTranslation->subtitle() : null);

        // Build translations
        if ($textChanged) {
            $translations = [];
            $translations[] = new ProcedureTranslation(null, $baseLang, null, $finalTitle, $finalSubtitle);

            foreach ($targetLanguages as $lang) {
                $translatedTitle = $this->translator->translate($finalTitle, $lang, $baseLang);
                $translatedSubtitle = $finalSubtitle ? $this->translator->translate($finalSubtitle, $lang, $baseLang) : null;
                $translations[] = new ProcedureTranslation(null, $lang, null, $translatedTitle, $translatedSubtitle);
            }
        } else {
            $translations = $procedure->translations();
        }

        // --- Handle Components: null means keep existing ---
        $sections = $sectionsData !== null
            ? $this->createSections($sectionsData, $baseLang, $targetLanguages)
            : $procedure->sections();

        $faqs = $faqsData !== null
            ? $this->createFaqs($faqsData, $baseLang, $targetLanguages)
            : $procedure->faqs();

        $instructions = $postoperativeInstructionsData !== null
            ? $this->createInstructions($postoperativeInstructionsData, $baseLang, $targetLanguages)
            : $procedure->postoperativeInstructions();

        $steps = $preparationStepsData !== null
            ? $this->createSteps($preparationStepsData, $baseLang, $targetLanguages)
            : $procedure->preparationSteps();

        $phases = $recoveryPhasesData !== null
            ? $this->createPhases($recoveryPhasesData, $baseLang, $targetLanguages)
            : $procedure->recoveryPhases();

        $gallery = $galleryData !== null
            ? array_map(function($g) {
                $pairId = isset($g['pairId']) && $g['pairId'] !== '' ? (int) $g['pairId'] : null;
                return new ProcedureResultGallery(null, $g['path'], $g['type'], $pairId, (int) ($g['order'] ?? 0));
            }, $galleryData)
            : $procedure->gallery();

        $updatedProcedure = new Procedure(
            $id,
            $finalUserId,
            $image ?? $procedure->image(),
            $finalCategoryCode,
            $finalStatus,
            $procedure->views(),
            $translations,
            $sections,
            $faqs,
            $instructions,
            $steps,
            $phases,
            $gallery
        );

        $this->repository->update($updatedProcedure);
    }

    private function createSections(array $data, string $baseLang, array $targetLanguages): array
    {
        return array_map(function($d) use ($baseLang, $targetLanguages) {
            $ts = [];
            $ts[] = new ProcedureSectionTranslation(null, $baseLang, $d['title'] ?? null, $d['contentOne'] ?? null, $d['contentTwo'] ?? null);
            foreach ($targetLanguages as $lang) {
                $tTitle = ($d['title'] ?? null) ? $this->translator->translate($d['title'], $lang, $baseLang) : null;
                $tC1 = ($d['contentOne'] ?? null) ? $this->translator->translate($d['contentOne'], $lang, $baseLang) : null;
                $tC2 = ($d['contentTwo'] ?? null) ? $this->translator->translate($d['contentTwo'], $lang, $baseLang) : null;
                $ts[] = new ProcedureSectionTranslation(null, $lang, $tTitle, $tC1, $tC2);
            }
            return new ProcedureSection(null, $d['type'], $d['image'] ?? null, $ts);
        }, $data);
    }

    private function createFaqs(array $data, string $baseLang, array $targetLanguages): array
    {
        return array_map(function($d) use ($baseLang, $targetLanguages) {
            $ts = [];
            $ts[] = new ProcedureFaqTranslation(null, $baseLang, $d['question'], $d['answer']);
            foreach ($targetLanguages as $lang) {
                $tQ = $this->translator->translate($d['question'], $lang, $baseLang);
                $tA = $this->translator->translate($d['answer'], $lang, $baseLang);
                $ts[] = new ProcedureFaqTranslation(null, $lang, $tQ, $tA);
            }
            return new ProcedureFaq(null, (int) ($d['order'] ?? 0), $ts);
        }, $data);
    }

    private function createInstructions(array $data, string $baseLang, array $targetLanguages): array
    {
        return array_map(function($d) use ($baseLang, $targetLanguages) {
            $ts = [];
            $ts[] = new ProcedurePostoperativeInstructionTranslation(null, $baseLang, $d['content']);
            foreach ($targetLanguages as $lang) {
                $tC = $this->translator->translate($d['content'], $lang, $baseLang);
                $ts[] = new ProcedurePostoperativeInstructionTranslation(null, $lang, $tC);
            }
            return new ProcedurePostoperativeInstruction(null, $d['type'], (int) ($d['order'] ?? 0), $ts);
        }, $data);
    }

    private function createSteps(array $data, string $baseLang, array $targetLanguages): array
    {
        return array_map(function($d) use ($baseLang, $targetLanguages) {
            $ts = [];
            $ts[] = new ProcedurePreparationStepTranslation(null, $baseLang, $d['title'], $d['description'] ?? null);
            foreach ($targetLanguages as $lang) {
                $tT = $this->translator->translate($d['title'], $lang, $baseLang);
                $tD = ($d['description'] ?? null) ? $this->translator->translate($d['description'], $lang, $baseLang) : null;
                $ts[] = new ProcedurePreparationStepTranslation(null, $lang, $tT, $tD);
            }
            return new ProcedurePreparationStep(null, (int) ($d['order'] ?? 0), $ts);
        }, $data);
    }

    private function createPhases(array $data, string $baseLang, array $targetLanguages): array
    {
        return array_map(function($d) use ($baseLang, $targetLanguages) {
            $ts = [];
            $ts[] = new ProcedureRecoveryPhaseTranslation(null, $baseLang, $d['period'] ?? null, $d['title'], $d['description'] ?? null);
            foreach ($targetLanguages as $lang) {
                $tP = ($d['period'] ?? null) ? $this->translator->translate($d['period'], $lang, $baseLang) : null;
                $tT = $this->translator->translate($d['title'], $lang, $baseLang);
                $tD = ($d['description'] ?? null) ? $this->translator->translate($d['description'], $lang, $baseLang) : null;
                $ts[] = new ProcedureRecoveryPhaseTranslation(null, $lang, $tP, $tT, $tD);
            }
            return new ProcedureRecoveryPhase(null, (int) ($d['order'] ?? 0), $ts);
        }, $data);
    }
}

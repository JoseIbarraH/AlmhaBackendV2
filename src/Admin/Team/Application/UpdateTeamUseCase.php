<?php

declare(strict_types=1);

namespace Src\Admin\Team\Application;

use Src\Admin\Team\Domain\Contracts\TeamRepositoryContract;
use Src\Admin\Team\Domain\Entity\Team;
use Src\Admin\Team\Domain\Entity\TeamTranslation;
use Src\Admin\Team\Domain\Entity\TeamImage;
use Src\Admin\Team\Domain\Entity\TeamImageTranslation;
use Src\Shared\Domain\Contracts\TranslatorServiceContract;
use RuntimeException;

final class UpdateTeamUseCase {
    private TeamRepositoryContract $repository;
    private TranslatorServiceContract $translator;

    public function __construct(TeamRepositoryContract $repo, TranslatorServiceContract $trans) {
        $this->repository = $repo;
        $this->translator = $trans;
    }

    /**
     * Patch-style update: null params mean "keep existing value".
     */
    public function execute(
        int $id,
        string $baseLang,
        ?string $name = null,
        ?string $status = null,
        ?string $userId = null,
        ?string $slug = null,
        ?string $image = null,
        ?string $specialization = null,
        ?string $description = null,
        ?string $biography = null,
        array $targetLanguages = [],
        ?array $images = null,
        bool $specializationWasSent = false,
        bool $descriptionWasSent = false,
        bool $biographyWasSent = false
    ): void {
        $existingTeam = $this->repository->findById($id);

        if (!$existingTeam) {
            throw new RuntimeException("Team member not found with ID: $id");
        }

        // Resolve scalar values
        $finalName = $name ?? $existingTeam->name();
        $finalStatus = $status ?? $existingTeam->status();
        $finalUserId = $userId ?? $existingTeam->userId();

        // Find existing baseLang translation
        $existingTranslation = $existingTeam->getTranslation($baseLang);
        if (!$existingTranslation && count($existingTeam->translations()) > 0) {
            $existingTranslation = $existingTeam->translations()[0];
        }

        // Determine if translatable content changed
        $textChanged = $specializationWasSent || $descriptionWasSent || $biographyWasSent;

        $finalSpecialization = $specializationWasSent ? $specialization : ($existingTranslation ? $existingTranslation->specialization() : null);
        $finalDescription = $descriptionWasSent ? $description : ($existingTranslation ? $existingTranslation->description() : null);
        $finalBiography = $biographyWasSent ? $biography : ($existingTranslation ? $existingTranslation->biography() : null);

        if ($textChanged) {
            $translations = [];
            $translations[] = new TeamTranslation($baseLang, $finalSpecialization, $finalDescription, $finalBiography);

            foreach ($targetLanguages as $lang) {
                $tSpecialization = $finalSpecialization ? $this->translator->translate($finalSpecialization, $lang, $baseLang) : null;
                $tDescription = $finalDescription ? $this->translator->translate($finalDescription, $lang, $baseLang) : null;
                $tBiography = $finalBiography ? $this->translator->translate($finalBiography, $lang, $baseLang) : null;
                $translations[] = new TeamTranslation($lang, $tSpecialization, $tDescription, $tBiography);
            }
        } else {
            $translations = $existingTeam->translations();
        }

        // Gallery: null means keep existing
        if ($images !== null) {
            $teamImages = [];
            foreach ($images as $imageData) {
                $imageTranslations = [];
                $imageTranslations[] = new TeamImageTranslation($baseLang, null);
                // Normalize any URL sent by the UI back to a relative path.
                $path = \Src\Shared\Infrastructure\Support\MediaUrl::toRelativePath($imageData['path']);
                $teamImages[] = new TeamImage(
                    $path,
                    (int) ($imageData['order'] ?? 0),
                    $imageTranslations
                );
            }
        } else {
            $teamImages = $existingTeam->images();
        }

        $finalImage = $image !== null
            ? \Src\Shared\Infrastructure\Support\MediaUrl::toRelativePath($image)
            : $existingTeam->image();

        $team = new Team(
            $slug ?? $existingTeam->slug(),
            $finalName,
            $finalStatus,
            $finalUserId,
            $finalImage,
            $translations,
            $teamImages,
            $id
        );

        $this->repository->update($team);
    }
}

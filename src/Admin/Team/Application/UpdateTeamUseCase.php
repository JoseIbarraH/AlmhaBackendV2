<?php

declare(strict_types=1);

namespace Src\Admin\Team\Application;

use Src\Admin\Team\Domain\Contracts\TeamRepositoryContract;
use Src\Admin\Team\Domain\Entity\Team;
use Src\Admin\Team\Domain\Entity\TeamTranslation;
use Src\Shared\Domain\Contracts\TranslatorServiceContract;

final class UpdateTeamUseCase {
    private TeamRepositoryContract $repository;
    private TranslatorServiceContract $translator;

    public function __construct(TeamRepositoryContract $repo, TranslatorServiceContract $trans) {
        $this->repository = $repo;
        $this->translator = $trans;
    }

    public function execute(
        int $id,
        ?string $userId = null,
        ?string $slug = null,
        string $baseLang,
        string $name,
        string $status,
        ?string $image = null,
        ?string $specialization = null,
        ?string $description = null,
        ?string $biography = null,
        array $targetLanguages = [],
        array $images = []
    ): void {
        $translations = [];
        $translations[] = new TeamTranslation($baseLang, $specialization, $description, $biography);

        foreach ($targetLanguages as $lang) {
            $tSpecialization = $specialization ? $this->translator->translate($specialization, $lang, $baseLang) : null;
            $tDescription = $description ? $this->translator->translate($description, $lang, $baseLang) : null;
            $tBiography = $biography ? $this->translator->translate($biography, $lang, $baseLang) : null;
            $translations[] = new TeamTranslation($lang, $tSpecialization, $tDescription, $tBiography);
        }

        $team = new Team(
            $slug,
            $name,
            $status,
            $userId,
            $image,
            $translations,
            $images,
            $id
        );

        $this->repository->update($team);
    }
}

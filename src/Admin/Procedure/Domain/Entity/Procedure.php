<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Domain\Entity;

use DateTime;
use RuntimeException;
use Src\Shared\Infrastructure\Support\MediaUrl;

final class Procedure implements \JsonSerializable
{
    private ?int $id;
    private ?string $userId;
    private ?string $image;
    private string $categoryCode;
    private string $status;
    private int $views;
    private ?string $title;

    /** @var ProcedureTranslation[] */
    private array $translations;

    /** @var ProcedureSection[] */
    private array $sections;

    /** @var ProcedureFaq[] */
    private array $faqs;

    /** @var ProcedurePostoperativeInstruction[] */
    private array $postoperativeInstructions;

    /** @var ProcedurePreparationStep[] */
    private array $preparationSteps;

    /** @var ProcedureRecoveryPhase[] */
    private array $recoveryPhases;

    /** @var ProcedureResultGallery[] */
    private array $gallery;

    public function __construct(
        ?int $id = null,
        ?string $userId = null,
        ?string $image = null,
        string $categoryCode,
        string $status = "draft",
        int $views = 0,
        array $translations = [],
        array $sections = [],
        array $faqs = [],
        array $postoperativeInstructions = [],
        array $preparationSteps = [],
        array $recoveryPhases = [],
        array $gallery = [],
        ?string $title = null
    ) {
        if (!in_array($status, ['draft', 'published', 'archived'])) {
            throw new RuntimeException("Invalid procedure status: $status");
        }

        $this->id = $id;
        $this->userId = $userId;
        $this->image = $image;
        $this->categoryCode = $categoryCode;
        $this->status = $status;
        $this->views = $views;
        $this->translations = $translations;
        $this->sections = $sections;
        $this->faqs = $faqs;
        $this->postoperativeInstructions = $postoperativeInstructions;
        $this->preparationSteps = $preparationSteps;
        $this->recoveryPhases = $recoveryPhases;
        $this->gallery = $gallery;
        $this->title = $title;
    }

    public function id(): ?int { return $this->id; }
    public function userId(): ?string { return $this->userId; }
    public function image(): ?string { return $this->image; }
    public function categoryCode(): ?string { return $this->categoryCode; }
    public function status(): ?string { return $this->status; }
    public function views(): ?int { return $this->views; }

    /** @return ProcedureTranslation[] */
    public function translations(): array { return $this->translations; }

    /** @return ProcedureSection[] */
    public function sections(): array { return $this->sections; }

    /** @return ProcedureFaq[] */
    public function faqs(): array { return $this->faqs; }

    /** @return ProcedurePostoperativeInstruction[] */
    public function postoperativeInstructions(): array { return $this->postoperativeInstructions; }

    /** @return ProcedurePreparationStep[] */
    public function preparationSteps(): array { return $this->preparationSteps; }

    /** @return ProcedureRecoveryPhase[] */
    public function recoveryPhases(): array { return $this->recoveryPhases; }

    /** @return ProcedureResultGallery[] */
    public function gallery(): array { return $this->gallery; }

    public function changeStatus(string $newStatus): void
    {
        if (!in_array($newStatus, ['draft', 'published', 'archived'])) {
            throw new RuntimeException("Invalid procedure status: $newStatus");
        }
        $this->status = $newStatus;
    }

    public function getTranslation(string $lang): ?ProcedureTranslation
    {
        foreach ($this->translations as $translation) {
            if ($translation->lang() === $lang) {
                return $translation;
            }
        }
        return null;
    }

    public function addSection(ProcedureSection $section): void { $this->sections[] = $section; }
    public function addFaq(ProcedureFaq $faq): void { $this->faqs[] = $faq; }
    public function addPostoperativeInstruction(ProcedurePostoperativeInstruction $instruction): void { $this->postoperativeInstructions[] = $instruction; }
    public function addPreparationStep(ProcedurePreparationStep $step): void { $this->preparationSteps[] = $step; }
    public function addRecoveryPhase(ProcedureRecoveryPhase $phase): void { $this->recoveryPhases[] = $phase; }
    public function addGalleryItem(ProcedureResultGallery $item): void { $this->gallery[] = $item; }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'image' => MediaUrl::resolve($this->image),
            'imagePath' => $this->image,
            'categoryCode' => $this->categoryCode,
            'status' => $this->status,
            'views' => $this->views,
            'title' => $this->title ?? (count($this->translations) > 0 ? $this->translations[0]->title() : null),
            'translations' => $this->translations,
            'sections' => $this->sections,
            'faqs' => $this->faqs,
            'postoperativeInstructions' => $this->postoperativeInstructions,
            'preparationSteps' => $this->preparationSteps,
            'recoveryPhases' => $this->recoveryPhases,
            'gallery' => $this->gallery,
        ];
    }
}

<?php

namespace Src\Admin\Design\Application;

use Src\Admin\Design\Domain\DesignRepositoryContract;

class UpdateDesignStatusUseCase
{
    /**
     * Pairs of design keys that are mutually exclusive — when one is activated
     * the other is forced to 'inactive'. Extend the array if you add more
     * A/B pairs in the future (e.g. seasonal variants).
     *
     * @var array<int, array{string, string}>
     */
    private const MUTUALLY_EXCLUSIVE_PAIRS = [
        ['main_banner', 'alternate_main_banner'],
    ];

    private DesignRepositoryContract $repository;

    public function __construct(DesignRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $designId, string $status): void
    {
        $this->repository->updateDesignStatus($designId, $status);

        // When activating a design that belongs to a mutually-exclusive pair,
        // deactivate its counterpart so only one is live at a time.
        if ($status === 'active') {
            $this->deactivateCounterpart($designId);
        }
    }

    private function deactivateCounterpart(int $activatedDesignId): void
    {
        $design = $this->repository->findById($activatedDesignId);
        if (!$design) {
            return;
        }

        $activatedKey = $design->key;
        $counterpartKey = $this->findCounterpart($activatedKey);
        if ($counterpartKey === null) {
            return;
        }

        $counterpart = $this->repository->findByKey($counterpartKey);
        if ($counterpart && $counterpart->status === 'active') {
            $this->repository->updateDesignStatus($counterpart->id, 'inactive');
        }
    }

    private function findCounterpart(string $key): ?string
    {
        foreach (self::MUTUALLY_EXCLUSIVE_PAIRS as [$a, $b]) {
            if ($key === $a) {
                return $b;
            }
            if ($key === $b) {
                return $a;
            }
        }

        return null;
    }
}

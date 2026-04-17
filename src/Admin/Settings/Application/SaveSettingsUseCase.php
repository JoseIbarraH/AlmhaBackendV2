<?php

namespace Src\Admin\Settings\Application;

use Src\Admin\Settings\Domain\SettingRepositoryContract;

class SaveSettingsUseCase
{
    public function __construct(
        private SettingRepositoryContract $repository
    ) {}

    public function execute(array $settings, string $group): void
    {
        $this->repository->saveMany($settings, $group);
    }
}

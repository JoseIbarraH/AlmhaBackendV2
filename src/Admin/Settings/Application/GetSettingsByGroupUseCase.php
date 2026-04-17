<?php

namespace Src\Admin\Settings\Application;

use Src\Admin\Settings\Domain\SettingRepositoryContract;

class GetSettingsByGroupUseCase
{
    public function __construct(
        private SettingRepositoryContract $repository
    ) {}

    public function execute(string $group): array
    {
        $settings = $this->repository->findByGroup($group);
        
        $response = [];
        foreach ($settings as $setting) {
            $response[$setting->key()] = $setting->value();
        }
        
        return $response;
    }
}

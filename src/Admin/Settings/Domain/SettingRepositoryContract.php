<?php

namespace Src\Admin\Settings\Domain;

interface SettingRepositoryContract
{
    /**
     * @return Setting[]
     */
    public function findByGroup(string $group): array;

    public function save(string $key, mixed $value, string $group): Setting;

    public function saveMany(array $settings, string $group): void;
}

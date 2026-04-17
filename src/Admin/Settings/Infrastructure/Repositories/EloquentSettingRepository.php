<?php

namespace Src\Admin\Settings\Infrastructure\Repositories;

use Src\Admin\Settings\Domain\Setting;
use Src\Admin\Settings\Domain\SettingRepositoryContract;
use Src\Admin\Settings\Infrastructure\Models\EloquentSettingModel;

class EloquentSettingRepository implements SettingRepositoryContract
{
    public function findByGroup(string $group): array
    {
        $models = EloquentSettingModel::where('group', $group)->get();
        return $models->map(fn(EloquentSettingModel $model): Setting => $this->toEntity($model))->toArray();
    }

    public function save(string $key, mixed $value, string $group): Setting
    {
        $model = EloquentSettingModel::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        return $this->toEntity($model);
    }

    public function saveMany(array $settings, string $group): void
    {
        foreach ($settings as $key => $value) {
            $this->save($key, $value, $group);
        }
    }

    private function toEntity(EloquentSettingModel $model): Setting
    {
        return new Setting(
            $model->id,
            $model->key,
            $model->value,
            $model->group
        );
    }
}

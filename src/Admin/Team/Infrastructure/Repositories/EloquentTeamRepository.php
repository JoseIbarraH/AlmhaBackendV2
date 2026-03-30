<?php

declare(strict_types=1);

namespace Src\Admin\Team\Infrastructure\Repositories;

use Src\Admin\Team\Infrastructure\Models\TeamEloquentModel;
use Src\Admin\Team\Domain\Contracts\TeamRepositoryContract;
use Src\Admin\Team\Domain\Entity\TeamTranslation;
use Src\Admin\Team\Domain\Entity\TeamImage;
use Src\Admin\Team\Domain\Entity\TeamImageTranslation;
use Src\Admin\Team\Domain\Entity\Team;
use Illuminate\Support\Facades\DB;



final class EloquentTeamRepository implements TeamRepositoryContract
{
    private TeamEloquentModel $model;

    public function __construct(TeamEloquentModel $model)
    {
        $this->model = $model;
    }

    public function save(Team $team): int
    {
        $teamId = 0;

        DB::transaction(function () use ($team, &$teamId) {
            $eloquentTeam = $this->model->create([
                'user_id' => $team->userId(),
                'name' => $team->name(),
                'status' => $team->status(),
                'image' => $team->image()
            ]);

            $teamId = $eloquentTeam->id;

            foreach ($team->translations() as $translation) {
                $eloquentTeam->translations()->create([
                    'lang' => $translation->lang(),
                    'specialization' => $translation->specialization(),
                    'description' => $translation->description(),
                    'biography' => $translation->biography()
                ]);
            }

            foreach ($team->images() as $image) {
                $eloquentImage = $eloquentTeam->images()->create([
                    'path' => $image->path(),
                    'order' => $image->order()
                ]);

                foreach ($image->translations() as $it) {
                    $eloquentImage->translations()->create([
                        'lang' => $it->lang(),
                        'description' => $it->description()
                    ]);
                }
            }
        });

        return $teamId;
    }

    public function findById(int $id): ?Team
    {
        $eloquentTeam = $this->model->with(['translations', 'images.translations'])->find($id);

        if (!$eloquentTeam) {
            return null;
        }

        return $this->toDomainEntity($eloquentTeam);
    }

    public function delete(int $id): void
    {
        $eloquentTeam = $this->model->find($id);
        if ($eloquentTeam) {
            $eloquentTeam->delete();
        }

    }

    public function getAll(): array
    {
        $teams = $this->model->with(['translations', 'images.translations'])->get();

        return $teams->map(function ($eloquentTeam) {
            return $this->toDomainEntity($eloquentTeam);
        })->toArray();
    }

    public function update(Team $team): void
    {
        if ($team->id() === null) {
            return;
        }

        DB::transaction(function () use ($team) {
            $eloquentTeam = $this->model->find($team->id());

            if ($eloquentTeam) {
                $eloquentTeam->update([
                    'user_id' => $team->userId(),
                    'name' => $team->name(),
                    'status' => $team->status(),
                    'image' => $team->image()
                ]);

                $eloquentTeam->translations()->delete();
                foreach ($team->translations() as $translation) {
                    $eloquentTeam->translations()->create([
                        'lang' => $translation->lang(),
                        'specialization' => $translation->specialization(),
                        'description' => $translation->description(),
                        'biography' => $translation->biography()
                    ]);
                }

                $eloquentTeam->images()->delete();
                foreach ($team->images() as $image) {
                    $eloquentImage = $eloquentTeam->images()->create([
                        'path' => $image->path(),
                        'order' => $image->order()
                    ]);

                    foreach ($image->translations() as $it) {
                        $eloquentImage->translations()->create([
                            'lang' => $it->lang(),
                            'description' => $it->description()
                        ]);
                    }
                }
            }
        });
    }

    public function updateImage(int $id, string $imageUrl): void
    {
        $eloquentTeam = $this->model->find($id);
        if ($eloquentTeam) {
            $eloquentTeam->update(['image' => $imageUrl]);
        }
    }

    private function toDomainEntity($eloquentTeam): Team
    {
        $translations = $eloquentTeam->translations->map(function ($t) {
            return new TeamTranslation(
                $t->lang,
                $t->specialization,
                $t->description,
                $t->biography,
                $t->id
            );
        })->toArray();

        $images = $eloquentTeam->images->map(function ($i) {
            $it = $i->translations->map(function ($it) {
                return new TeamImageTranslation(
                    $it->lang,
                    $it->description,
                    $it->id
                );
            })->toArray();
            return new TeamImage(
                $i->path,
                $i->order,
                $it,
                $i->id
            );
        })->toArray();

        return new Team(
            $eloquentTeam->slug,
            $eloquentTeam->name,
            $eloquentTeam->status,
            $eloquentTeam->user_id,
            $eloquentTeam->image,
            $translations,
            $images,
            $eloquentTeam->id,
        );
    }

}

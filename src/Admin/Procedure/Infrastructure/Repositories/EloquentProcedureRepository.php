<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
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
use Src\Admin\Procedure\Infrastructure\Models\ProcedureEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureTranslationEloquentModel;

final class EloquentProcedureRepository implements ProcedureRepositoryContract
{
    private ProcedureEloquentModel $model;

    public function __construct(ProcedureEloquentModel $model)
    {
        $this->model = $model;
    }

    public function save(Procedure $procedure): int
    {
        $procedureId = 0;

        DB::transaction(function () use ($procedure, &$procedureId) {
            $eloquentProcedure = $this->model->create([
                'user_id' => $procedure->userId(),
                'image' => $procedure->image(),
                'category_code' => $procedure->categoryCode(),
                'status' => $procedure->status(),
                'views' => $procedure->views(),
            ]);

            $procedureId = $eloquentProcedure->id;

            // Translations
            foreach ($procedure->translations() as $t) {
                $eloquentProcedure->translations()->create([
                    'lang' => $t->lang(),
                    'title' => $t->title(),
                    'subtitle' => $t->subtitle(),
                ]);
            }

            // Sections
            foreach ($procedure->sections() as $s) {
                $section = $eloquentProcedure->sections()->create([
                    'type' => $s->type(),
                    'image' => $s->image(),
                ]);
                foreach ($s->translations() as $st) {
                    $section->translations()->create([
                        'lang' => $st->lang(),
                        'title' => $st->title(),
                        'content_one' => $st->contentOne(),
                        'content_two' => $st->contentTwo(),
                    ]);
                }
            }

            // FAQs
            foreach ($procedure->faqs() as $f) {
                $faq = $eloquentProcedure->faqs()->create(['order' => $f->order()]);
                foreach ($f->translations() as $ft) {
                    $faq->translations()->create([
                        'lang' => $ft->lang(),
                        'question' => $ft->question(),
                        'answer' => $ft->answer(),
                    ]);
                }
            }

            // Postoperative Instructions
            foreach ($procedure->postoperativeInstructions() as $pi) {
                $instruction = $eloquentProcedure->postoperativeInstructions()->create([
                    'type' => $pi->type(),
                    'order' => $pi->order(),
                ]);
                foreach ($pi->translations() as $pit) {
                    $instruction->translations()->create([
                        'lang' => $pit->lang(),
                        'content' => $pit->content(),
                    ]);
                }
            }

            // Preparation Steps
            foreach ($procedure->preparationSteps() as $ps) {
                $step = $eloquentProcedure->preparationSteps()->create(['order' => $ps->order()]);
                foreach ($ps->translations() as $pst) {
                    $step->translations()->create([
                        'lang' => $pst->lang(),
                        'title' => $pst->title(),
                        'description' => $pst->description(),
                    ]);
                }
            }

            // Recovery Phases
            foreach ($procedure->recoveryPhases() as $rp) {
                $phase = $eloquentProcedure->recoveryPhases()->create(['order' => $rp->order()]);
                foreach ($rp->translations() as $rpt) {
                    $phase->translations()->create([
                        'lang' => $rpt->lang(),
                        'period' => $rpt->period(),
                        'title' => $rpt->title(),
                        'description' => $rpt->description(),
                    ]);
                }
            }

            // Gallery
            foreach ($procedure->gallery() as $g) {
                $eloquentProcedure->gallery()->create([
                    'path' => $g->path(),
                    'type' => $g->type(),
                    'pair_id' => $g->pairId(),
                    'order' => $g->order(),
                ]);
            }
        });

        return $procedureId;
    }

    public function update(Procedure $procedure): void
    {
        if ($procedure->id() === null) {
            return;
        }

        DB::transaction(fn () => $this->doUpdate($procedure));
    }

    private function doUpdate(Procedure $procedure): void
    {
        /** @var ProcedureEloquentModel|null $eloquentProcedure */
        $eloquentProcedure = $this->model->find($procedure->id());

        if (!$eloquentProcedure) {
            return;
        }

        $eloquentProcedure->update([
            'user_id' => $procedure->userId(),
            'image' => $procedure->image(),
            'category_code' => $procedure->categoryCode(),
            'status' => $procedure->status(),
            'views' => $procedure->views(),
        ]);

        $this->clearRelations($eloquentProcedure);
        $this->syncTranslations($eloquentProcedure, $procedure);
        $this->syncSections($eloquentProcedure, $procedure);
        $this->syncFaqs($eloquentProcedure, $procedure);
        $this->syncPostoperativeInstructions($eloquentProcedure, $procedure);
        $this->syncPreparationSteps($eloquentProcedure, $procedure);
        $this->syncRecoveryPhases($eloquentProcedure, $procedure);
        $this->syncGallery($eloquentProcedure, $procedure);
    }

    private function clearRelations(ProcedureEloquentModel $eloquentProcedure): void
    {
        $eloquentProcedure->translations()->delete();
        $eloquentProcedure->sections()->delete();
        $eloquentProcedure->faqs()->delete();
        $eloquentProcedure->postoperativeInstructions()->delete();
        $eloquentProcedure->preparationSteps()->delete();
        $eloquentProcedure->recoveryPhases()->delete();
        $eloquentProcedure->gallery()->delete();
    }

    private function syncTranslations(ProcedureEloquentModel $eloquentProcedure, Procedure $procedure): void
    {
        foreach ($procedure->translations() as $t) {
            $eloquentProcedure->translations()->create([
                'lang' => $t->lang(),
                'title' => $t->title(),
                'subtitle' => $t->subtitle(),
            ]);
        }
    }

    private function syncSections(ProcedureEloquentModel $eloquentProcedure, Procedure $procedure): void
    {
        foreach ($procedure->sections() as $s) {
            $section = $eloquentProcedure->sections()->create([
                'type' => $s->type(),
                'image' => $s->image(),
            ]);
            foreach ($s->translations() as $st) {
                $section->translations()->create([
                    'lang' => $st->lang(),
                    'title' => $st->title(),
                    'content_one' => $st->contentOne(),
                    'content_two' => $st->contentTwo(),
                ]);
            }
        }
    }

    private function syncFaqs(ProcedureEloquentModel $eloquentProcedure, Procedure $procedure): void
    {
        foreach ($procedure->faqs() as $f) {
            $faq = $eloquentProcedure->faqs()->create(['order' => $f->order()]);
            foreach ($f->translations() as $ft) {
                $faq->translations()->create([
                    'lang' => $ft->lang(),
                    'question' => $ft->question(),
                    'answer' => $ft->answer(),
                ]);
            }
        }
    }

    private function syncPostoperativeInstructions(ProcedureEloquentModel $eloquentProcedure, Procedure $procedure): void
    {
        foreach ($procedure->postoperativeInstructions() as $pi) {
            $instruction = $eloquentProcedure->postoperativeInstructions()->create([
                'type' => $pi->type(),
                'order' => $pi->order(),
            ]);
            foreach ($pi->translations() as $pit) {
                $instruction->translations()->create([
                    'lang' => $pit->lang(),
                    'content' => $pit->content(),
                ]);
            }
        }
    }

    private function syncPreparationSteps(ProcedureEloquentModel $eloquentProcedure, Procedure $procedure): void
    {
        foreach ($procedure->preparationSteps() as $ps) {
            $step = $eloquentProcedure->preparationSteps()->create(['order' => $ps->order()]);
            foreach ($ps->translations() as $pst) {
                $step->translations()->create([
                    'lang' => $pst->lang(),
                    'title' => $pst->title(),
                    'description' => $pst->description(),
                ]);
            }
        }
    }

    private function syncRecoveryPhases(ProcedureEloquentModel $eloquentProcedure, Procedure $procedure): void
    {
        foreach ($procedure->recoveryPhases() as $rp) {
            $phase = $eloquentProcedure->recoveryPhases()->create(['order' => $rp->order()]);
            foreach ($rp->translations() as $rpt) {
                $phase->translations()->create([
                    'lang' => $rpt->lang(),
                    'period' => $rpt->period(),
                    'title' => $rpt->title(),
                    'description' => $rpt->description(),
                ]);
            }
        }
    }

    private function syncGallery(ProcedureEloquentModel $eloquentProcedure, Procedure $procedure): void
    {
        foreach ($procedure->gallery() as $g) {
            $eloquentProcedure->gallery()->create([
                'path' => $g->path(),
                'type' => $g->type(),
                'pair_id' => $g->pairId(),
                'order' => $g->order(),
            ]);
        }
    }

    public function findById(int $id, ?string $lang = null): ?Procedure
    {
        $eloquentProcedure = $this->model->with([
            'translations' => function ($q) use ($lang) {
                if ($lang) $q->where('lang', $lang);
            },
            'sections.translations' => function ($q) use ($lang) {
                if ($lang) $q->where('lang', $lang);
            },
            'faqs.translations' => function ($q) use ($lang) {
                if ($lang) $q->where('lang', $lang);
            },
            'postoperativeInstructions.translations' => function ($q) use ($lang) {
                if ($lang) $q->where('lang', $lang);
            },
            'preparationSteps.translations' => function ($q) use ($lang) {
                if ($lang) $q->where('lang', $lang);
            },
            'recoveryPhases.translations' => function ($q) use ($lang) {
                if ($lang) $q->where('lang', $lang);
            },
            'gallery'
        ])->find($id);

        if (!$eloquentProcedure) {
            return null;
        }

        return $this->toDomainEntity($eloquentProcedure);
    }

    public function findBySlug(string $slug, string $lang): ?Procedure
    {
        $translationModel = ProcedureTranslationEloquentModel::where('slug', $slug)
            ->where('lang', $lang)
            ->first();

        if (!$translationModel) {
            return null;
        }

        $eloquentProcedure = $this->model->with(['translations' => function ($query) use ($lang) {
            $query->where('lang', $lang);
        }])->find($translationModel->procedure_id);

        if (!$eloquentProcedure) {
            return null;
        }

        return $this->toDomainEntity($eloquentProcedure);
    }

    public function updateImage(int $id, string $imagePath): void
    {
        /** @var ProcedureEloquentModel|null $eloquentProcedure */
        $eloquentProcedure = $this->model->find($id);
        if ($eloquentProcedure) {
            $eloquentProcedure->update(['image' => $imagePath]);
        }
    }

    public function delete(int $id): void
    {
        /** @var ProcedureEloquentModel|null $eloquentProcedure */
        $eloquentProcedure = $this->model->find($id);
        if ($eloquentProcedure) {
            $eloquentProcedure->delete();
        }
    }

    public function getAll(int $page = 1, int $perPage = 15, ?string $search = null, ?string $status = null): array
    {
        $paginator = $this->model->with('translations')
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($search, function ($query, $search) {
                $query->whereHas('translations', function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('subtitle', 'like', "%{$search}%");
                });
            })
            ->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function ($eloquentProcedure) {
            return $this->toDomainEntity($eloquentProcedure);
        })->toArray();

        return [
            'items' => $items,
            'meta' => collect($paginator->toArray())->except('data')->toArray()
        ];
    }

    public function getAllByLang(string $lang, int $page = 1, int $perPage = 15, ?string $search = null, ?string $status = null): array
    {
        // Traeremos TODAS las traducciones para que toDomainEntity pueda aplicar el fallback
        $paginator = $this->model->with(['translations'])
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($search, function ($query, $search) use ($lang) {
                $query->whereHas('translations', function ($query) use ($search, $lang) {
                    $query->where('lang', $lang)
                        ->where(function ($query) use ($search) {
                            $query->where('title', 'like', "%{$search}%")
                                ->orWhere('subtitle', 'like', "%{$search}%");
                        });
                });
            })
            ->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function ($eloquentProcedure) use ($lang) {
            return $this->toDomainEntity($eloquentProcedure, $lang);
        })->toArray();

        return [
            'items' => $items,
            'meta' => collect($paginator->toArray())->except('data')->toArray()
        ];
    }

    /**
     * Convierte un modelo Eloquent a entidad de dominio
     */
    private function toDomainEntity(ProcedureEloquentModel $eloquentProcedure, ?string $lang = null): Procedure
    {
        $translations = $eloquentProcedure->translations->map(function ($t) {
            return new ProcedureTranslation($t->id, $t->lang, $t->slug, $t->title, $t->subtitle);
        })->toArray();

        if ($lang) {
            usort($translations, function($a, $b) use ($lang) {
                if ($a->lang() === $lang) return -1;
                if ($b->lang() === $lang) return 1;
                return 0;
            });
        }

        $localizedTitle = null;
        if ($lang) {
            $translationModel = $eloquentProcedure->translations->firstWhere('lang', $lang)
                                ?? $eloquentProcedure->translations->first();
            $localizedTitle = $translationModel?->title;
        }

        $sections = $eloquentProcedure->sections->map(function ($s) {
            $st = $s->translations->map(function ($st) {
                return new ProcedureSectionTranslation($st->id, $st->lang, $st->title, $st->content_one, $st->content_two);
            })->toArray();
            return new ProcedureSection($s->id, $s->type, $s->image, $st);
        })->toArray();

        $faqs = $eloquentProcedure->faqs->map(function ($f) {
            $ft = $f->translations->map(function ($ft) {
                return new ProcedureFaqTranslation($ft->id, $ft->lang, $ft->question, $ft->answer);
            })->toArray();
            return new ProcedureFaq($f->id, (int) $f->order, $ft);
        })->toArray();

        $instructions = $eloquentProcedure->postoperativeInstructions->map(function ($pi) {
            $pit = $pi->translations->map(function ($pit) {
                return new ProcedurePostoperativeInstructionTranslation($pit->id, $pit->lang, $pit->content);
            })->toArray();
            return new ProcedurePostoperativeInstruction($pi->id, $pi->type, (int) $pi->order, $pit);
        })->toArray();

        $steps = $eloquentProcedure->preparationSteps->map(function ($ps) {
            $pst = $ps->translations->map(function ($pst) {
                return new ProcedurePreparationStepTranslation($pst->id, $pst->lang, $pst->title, $pst->description);
            })->toArray();
            return new ProcedurePreparationStep($ps->id, (int) $ps->order, $pst);
        })->toArray();

        $phases = $eloquentProcedure->recoveryPhases->map(function ($rp) {
            $rpt = $rp->translations->map(function ($rpt) {
                return new ProcedureRecoveryPhaseTranslation($rpt->id, $rpt->lang, $rpt->period, $rpt->title, $rpt->description);
            })->toArray();
            return new ProcedureRecoveryPhase($rp->id, (int) $rp->order, $rpt);
        })->toArray();

        $gallery = $eloquentProcedure->gallery->map(function ($g) {
            $pairId = $g->pair_id !== null ? (int) $g->pair_id : null;
            return new ProcedureResultGallery($g->id, $g->path, $g->type, $pairId, (int) $g->order);
        })->toArray();

        return new Procedure(
            $eloquentProcedure->id,
            $eloquentProcedure->user_id,
            $eloquentProcedure->image,
            $eloquentProcedure->category_code,
            $eloquentProcedure->status,
            $eloquentProcedure->views,
            $translations,
            $sections,
            $faqs,
            $instructions,
            $steps,
            $phases,
            $gallery,
            $localizedTitle
        );
    }
}

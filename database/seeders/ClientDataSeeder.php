<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureCategoryEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureCategoryTranslationEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureFaqEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureFaqTranslationEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedurePostoperativeInstructionEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedurePostoperativeInstructionTranslationEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedurePreparationStepEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedurePreparationStepTranslationEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureRecoveryPhaseEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureRecoveryPhaseTranslationEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureResultGalleryEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureSectionEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureSectionTranslationEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureTranslationEloquentModel;
use Src\Admin\Team\Infrastructure\Models\TeamEloquentModel;
use Src\Admin\Team\Infrastructure\Models\TeamImageEloquentModel;
use Src\Admin\Team\Infrastructure\Models\TeamImageTranslationEloquentModel;
use Src\Admin\Team\Infrastructure\Models\TeamTranslationEloquentModel;

/**
 * Populates the DB with sample data so every /api/client/* endpoint returns
 * meaningful content for local development and frontend smoke testing.
 */
final class ClientDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedProcedureCategories();
        $this->seedProcedures();
        $this->seedTeam();
        $this->seedDesignMedia();

        $this->command->info('✅ Client sample data seeded');
    }

    private function seedProcedureCategories(): void
    {
        $categories = [
            'facial'   => ['es' => 'Facial',   'en' => 'Facial'],
            'corporal' => ['es' => 'Corporal', 'en' => 'Body'],
        ];

        foreach ($categories as $code => $titles) {
            $cat = ProcedureCategoryEloquentModel::firstOrCreate(['code' => $code]);

            foreach ($titles as $lang => $title) {
                ProcedureCategoryTranslationEloquentModel::updateOrCreate(
                    ['category_id' => $cat->id, 'lang' => $lang],
                    ['title' => $title]
                );
            }
        }
    }

    private function seedProcedures(): void
    {
        $samples = [
            [
                'category_code' => 'facial',
                'views'         => 1205,
                'image'         => '/images/procedures/rinoplastia.jpg',
                'translations'  => [
                    'es' => ['title' => 'Rinoplastia', 'subtitle' => 'Armonía y proporción facial', 'slug' => 'rinoplastia'],
                    'en' => ['title' => 'Rhinoplasty', 'subtitle' => 'Facial harmony and proportion', 'slug' => 'rhinoplasty'],
                ],
            ],
            [
                'category_code' => 'corporal',
                'views'         => 2380,
                'image'         => '/images/procedures/abdominoplastia.jpg',
                'translations'  => [
                    'es' => ['title' => 'Abdominoplastia', 'subtitle' => 'Contorno abdominal firme', 'slug' => 'abdominoplastia'],
                    'en' => ['title' => 'Tummy Tuck',      'subtitle' => 'Firm abdominal contour',   'slug' => 'tummy-tuck'],
                ],
            ],
            [
                'category_code' => 'corporal',
                'views'         => 1870,
                'image'         => '/images/procedures/liposuccion.jpg',
                'translations'  => [
                    'es' => ['title' => 'Liposucción',    'subtitle' => 'Remodelado de silueta',   'slug' => 'liposuccion'],
                    'en' => ['title' => 'Liposuction',    'subtitle' => 'Silhouette remodeling',   'slug' => 'liposuction'],
                ],
            ],
        ];

        foreach ($samples as $s) {
            $procedure = ProcedureEloquentModel::create([
                'category_code' => $s['category_code'],
                'status'        => 'published',
                'image'         => $s['image'],
                'views'         => $s['views'],
            ]);

            foreach ($s['translations'] as $lang => $t) {
                ProcedureTranslationEloquentModel::create([
                    'procedure_id' => $procedure->id,
                    'lang'         => $lang,
                    'title'        => $t['title'],
                    'subtitle'     => $t['subtitle'],
                    'slug'         => $t['slug'],
                ]);
            }

            $this->seedProcedureSections($procedure->id);
            $this->seedProcedurePreparation($procedure->id);
            $this->seedProcedureRecovery($procedure->id);
            $this->seedProcedurePostop($procedure->id);
            $this->seedProcedureFaqs($procedure->id);
            $this->seedProcedureGallery($procedure->id);
        }
    }

    private function seedProcedureSections(int $procedureId): void
    {
        $sections = [
            'what_is'   => [
                'es' => ['title' => '¿En qué consiste?',   'one' => 'Descripción del procedimiento en español.', 'two' => 'Detalle adicional del procedimiento.'],
                'en' => ['title' => 'What is it?',         'one' => 'Description of the procedure in English.',   'two' => 'Additional procedure details.'],
            ],
            'technique' => [
                'es' => ['title' => 'Técnica quirúrgica',  'one' => 'Explicación de la técnica.', 'two' => 'Más detalles técnicos.'],
                'en' => ['title' => 'Surgical technique', 'one' => 'Technique explanation.',    'two' => 'More technical details.'],
            ],
            'recovery'  => [
                'es' => ['title' => 'Recuperación',        'one' => 'Visión general de recuperación.', 'two' => 'Tiempos esperados.'],
                'en' => ['title' => 'Recovery',            'one' => 'Recovery overview.',              'two' => 'Expected timelines.'],
            ],
        ];

        foreach ($sections as $type => $translations) {
            $section = ProcedureSectionEloquentModel::create([
                'procedure_id' => $procedureId,
                'type'         => $type,
                'image'        => null,
            ]);

            foreach ($translations as $lang => $content) {
                ProcedureSectionTranslationEloquentModel::create([
                    'procedure_section_id' => $section->id,
                    'lang'                 => $lang,
                    'title'                => $content['title'],
                    'content_one'          => $content['one'],
                    'content_two'          => $content['two'],
                ]);
            }
        }
    }

    private function seedProcedurePreparation(int $procedureId): void
    {
        $steps = [
            ['es' => ['title' => 'Valoración médica',   'description' => 'Consulta inicial con el especialista.'],
             'en' => ['title' => 'Medical evaluation', 'description' => 'Initial consultation with the specialist.']],
            ['es' => ['title' => 'Exámenes previos',    'description' => 'Laboratorios y estudios requeridos.'],
             'en' => ['title' => 'Pre-op tests',       'description' => 'Required lab work and imaging.']],
        ];

        foreach ($steps as $i => $content) {
            $step = ProcedurePreparationStepEloquentModel::create([
                'procedure_id' => $procedureId,
                'order'        => $i + 1,
            ]);

            foreach ($content as $lang => $t) {
                ProcedurePreparationStepTranslationEloquentModel::create([
                    'procedure_preparation_step_id' => $step->id,
                    'lang'                          => $lang,
                    'title'                         => $t['title'],
                    'description'                   => $t['description'],
                ]);
            }
        }
    }

    private function seedProcedureRecovery(int $procedureId): void
    {
        $phases = [
            ['es' => ['period' => 'Semana 1', 'title' => 'Reposo inicial',   'description' => 'Descanso y medicación.'],
             'en' => ['period' => 'Week 1',   'title' => 'Initial rest',    'description' => 'Rest and medication.']],
            ['es' => ['period' => 'Semana 2', 'title' => 'Movilidad suave', 'description' => 'Retomar actividades ligeras.'],
             'en' => ['period' => 'Week 2',   'title' => 'Gentle mobility','description' => 'Resume light activities.']],
            ['es' => ['period' => 'Mes 1',    'title' => 'Evaluación',       'description' => 'Control y seguimiento.'],
             'en' => ['period' => 'Month 1',  'title' => 'Check-up',        'description' => 'Follow-up appointment.']],
        ];

        foreach ($phases as $i => $content) {
            $phase = ProcedureRecoveryPhaseEloquentModel::create([
                'procedure_id' => $procedureId,
                'order'        => $i + 1,
            ]);

            foreach ($content as $lang => $t) {
                ProcedureRecoveryPhaseTranslationEloquentModel::create([
                    'procedure_recovery_phase_id' => $phase->id,
                    'lang'                        => $lang,
                    'period'                      => $t['period'],
                    'title'                       => $t['title'],
                    'description'                 => $t['description'],
                ]);
            }
        }
    }

    private function seedProcedurePostop(int $procedureId): void
    {
        $items = [
            ['type' => 'do',   'es' => 'Seguir las indicaciones médicas al pie de la letra.', 'en' => 'Follow medical instructions exactly.'],
            ['type' => 'do',   'es' => 'Mantener buena hidratación.',                          'en' => 'Stay well hydrated.'],
            ['type' => 'dont', 'es' => 'Evitar el ejercicio intenso durante la recuperación.', 'en' => 'Avoid intense exercise during recovery.'],
            ['type' => 'dont', 'es' => 'No consumir alcohol durante la primera semana.',       'en' => 'Do not drink alcohol during the first week.'],
        ];

        foreach ($items as $i => $item) {
            $instruction = ProcedurePostoperativeInstructionEloquentModel::create([
                'procedure_id' => $procedureId,
                'type'         => $item['type'],
                'order'        => $i + 1,
            ]);

            foreach (['es', 'en'] as $lang) {
                ProcedurePostoperativeInstructionTranslationEloquentModel::create([
                    'procedure_postoperative_instruction_id' => $instruction->id,
                    'lang'                                   => $lang,
                    'content'                                => $item[$lang],
                ]);
            }
        }
    }

    private function seedProcedureFaqs(int $procedureId): void
    {
        $faqs = [
            ['es' => ['q' => '¿Cuánto dura el procedimiento?', 'a' => 'Generalmente entre 2 y 4 horas.'],
             'en' => ['q' => 'How long does the procedure take?', 'a' => 'Generally between 2 and 4 hours.']],
            ['es' => ['q' => '¿Es doloroso?', 'a' => 'Se realiza bajo anestesia; el postoperatorio es manejable.'],
             'en' => ['q' => 'Is it painful?', 'a' => 'Performed under anesthesia; post-op is manageable.']],
        ];

        foreach ($faqs as $i => $content) {
            $faq = ProcedureFaqEloquentModel::create([
                'procedure_id' => $procedureId,
                'order'        => $i + 1,
            ]);

            foreach ($content as $lang => $t) {
                ProcedureFaqTranslationEloquentModel::create([
                    'procedure_faq_id' => $faq->id,
                    'lang'             => $lang,
                    'question'         => $t['q'],
                    'answer'           => $t['a'],
                ]);
            }
        }
    }

    private function seedProcedureGallery(int $procedureId): void
    {
        for ($i = 1; $i <= 2; $i++) {
            ProcedureResultGalleryEloquentModel::create([
                'procedure_id' => $procedureId,
                'path'         => "/images/gallery/procedure-{$procedureId}-{$i}.jpg",
                'type'         => 'after',
                'pair_id'      => $i,
                'order'        => $i,
            ]);
        }
    }

    private function seedTeam(): void
    {
        $members = [
            [
                'name' => 'Dra. Ana García',
                'image' => '/images/team/ana-garcia.jpg',
                'translations' => [
                    'es' => ['specialization' => 'Cirugía plástica y reconstructiva', 'description' => 'Cirujana especializada en procedimientos faciales y corporales.', 'biography' => '<p>Más de 10 años de experiencia.</p>'],
                    'en' => ['specialization' => 'Plastic and reconstructive surgery', 'description' => 'Surgeon specialized in facial and body procedures.', 'biography' => '<p>Over 10 years of experience.</p>'],
                ],
            ],
            [
                'name' => 'Dr. Carlos Pérez',
                'image' => '/images/team/carlos-perez.jpg',
                'translations' => [
                    'es' => ['specialization' => 'Cirugía estética', 'description' => 'Especialista en procedimientos mínimamente invasivos.', 'biography' => '<p>Certificado internacionalmente.</p>'],
                    'en' => ['specialization' => 'Aesthetic surgery', 'description' => 'Specialist in minimally invasive procedures.', 'biography' => '<p>Internationally certified.</p>'],
                ],
            ],
        ];

        foreach ($members as $m) {
            $team = TeamEloquentModel::create([
                'name'   => $m['name'],
                'slug'   => Str::slug($m['name']),
                'status' => 'active',
                'image'  => $m['image'],
            ]);

            foreach ($m['translations'] as $lang => $t) {
                TeamTranslationEloquentModel::create([
                    'team_id'        => $team->id,
                    'lang'           => $lang,
                    'specialization' => $t['specialization'],
                    'description'    => $t['description'],
                    'biography'      => $t['biography'],
                ]);
            }

            for ($i = 1; $i <= 2; $i++) {
                $img = TeamImageEloquentModel::create([
                    'team_id' => $team->id,
                    'path'    => "/images/team/gallery/team-{$team->id}-{$i}.jpg",
                    'order'   => $i,
                ]);

                foreach (['es', 'en'] as $lang) {
                    TeamImageTranslationEloquentModel::create([
                        'team_image_id' => $img->id,
                        'lang'          => $lang,
                        'description'   => $lang === 'es'
                            ? "Resultado caso {$i}"
                            : "Case {$i} result",
                    ]);
                }
            }
        }
    }

    private function seedDesignMedia(): void
    {
        // Populate the empty design_items created by DesignModuleSeeder with real media/translations.
        $mediaByKey = [
            'main_banner'      => [
                ['path' => '/images/banners/banner-1.jpg', 'es' => ['title' => 'Transformaciones que inspiran', 'subtitle' => 'Cirugía estética de clase mundial'],
                                                          'en' => ['title' => 'Transformations that inspire',    'subtitle' => 'World-class aesthetic surgery']],
                ['path' => '/images/banners/banner-2.jpg', 'es' => ['title' => 'Tu mejor versión',                'subtitle' => 'Equipo profesional certificado'],
                                                          'en' => ['title' => 'Your best version',               'subtitle' => 'Certified professional team']],
            ],
            'background_1'     => [['path' => '/images/backgrounds/bg-1.jpg', 'es' => ['title' => 'Excelencia', 'subtitle' => ''], 'en' => ['title' => 'Excellence', 'subtitle' => '']]],
            'background_2'     => [['path' => '/images/backgrounds/bg-2.jpg', 'es' => ['title' => 'Confianza',  'subtitle' => ''], 'en' => ['title' => 'Trust',      'subtitle' => '']]],
            'background_3'     => [['path' => '/images/backgrounds/bg-3.jpg', 'es' => ['title' => 'Resultados', 'subtitle' => ''], 'en' => ['title' => 'Results',    'subtitle' => '']]],
            'brands_carousel'  => [
                ['path' => '/images/brands/brand-1.png', 'es' => ['title' => 'Socio 1', 'subtitle' => ''], 'en' => ['title' => 'Partner 1', 'subtitle' => '']],
                ['path' => '/images/brands/brand-2.png', 'es' => ['title' => 'Socio 2', 'subtitle' => ''], 'en' => ['title' => 'Partner 2', 'subtitle' => '']],
            ],
        ];

        foreach ($mediaByKey as $key => $items) {
            $designId = DB::table('designs')->where('key', $key)->value('id');
            if (!$designId) {
                continue;
            }

            // Reset existing items for this design
            $existingItemIds = DB::table('design_items')->where('design_id', $designId)->pluck('id');
            DB::table('design_item_translations')->whereIn('design_item_id', $existingItemIds)->delete();
            DB::table('design_items')->where('design_id', $designId)->delete();

            foreach ($items as $i => $item) {
                $itemId = DB::table('design_items')->insertGetId([
                    'design_id'  => $designId,
                    'media_type' => 'image',
                    'media_path' => $item['path'],
                    'order'      => $i + 1,
                    'status'     => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach (['es', 'en'] as $lang) {
                    DB::table('design_item_translations')->insert([
                        'design_item_id' => $itemId,
                        'lang'           => $lang,
                        'title'          => $item[$lang]['title']    ?? null,
                        'subtitle'       => $item[$lang]['subtitle'] ?? null,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
            }
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DesignModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designs = [
            [
                'key' => 'background_1',
                'display_mode' => 'single_image',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'background_2',
                'display_mode' => 'single_image',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'background_3',
                'display_mode' => 'single_image',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'hero_section',
                'display_mode' => 'carousel',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'interleaved_1',
                'display_mode' => 'single_image',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($designs as $designData) {
            $designId = DB::table('designs')->insertGetId($designData);

            // Insert 1 empty design_item for each by default, so they can just update it
            // Only insert multiple for the carousel if we wanted to mock it, but 1 is fine to start with.
            $itemId = DB::table('design_items')->insertGetId([
                'design_id' => $designId,
                'media_type' => 'image',
                'media_path' => null,
                'order' => 1,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add english/spanish empty translation for the frontend to be ready
            DB::table('design_item_translations')->insert([
                [
                    'design_item_id' => $itemId,
                    'lang' => 'en',
                    'title' => null,
                    'subtitle' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'design_item_id' => $itemId,
                    'lang' => 'es',
                    'title' => null,
                    'subtitle' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }
    }
}

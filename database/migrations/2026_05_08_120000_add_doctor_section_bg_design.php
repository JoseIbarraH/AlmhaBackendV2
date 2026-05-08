<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DESIGN_ORDER = [
        'doctor_section_bg'     => 0,
        'main_banner'           => 10,
        'alternate_main_banner' => 20,
        'background_1'          => 30,
        'background_2'          => 40,
        'background_3'          => 50,
        'brands_carousel'       => 60,
    ];

    public function up(): void
    {
        // Stable display order for the admin appearance section.
        if (!Schema::hasColumn('designs', 'order')) {
            Schema::table('designs', function (Blueprint $table) {
                $table->integer('order')->default(1000)->after('status');
            });
        }

        // Backfill order for existing rows (idempotent).
        foreach (self::DESIGN_ORDER as $key => $order) {
            DB::table('designs')->where('key', $key)->update(['order' => $order]);
        }

        // Insert the new doctor_section_bg row only if missing — safe in production
        // (won't duplicate) and harmless in fresh installs (the seeder truncates afterwards).
        $exists = DB::table('designs')->where('key', 'doctor_section_bg')->exists();
        if (!$exists) {
            $now = now();

            $designId = DB::table('designs')->insertGetId([
                'key'          => 'doctor_section_bg',
                'display_mode' => 'single_image',
                'status'       => 'active',
                'order'        => self::DESIGN_ORDER['doctor_section_bg'],
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);

            $itemId = DB::table('design_items')->insertGetId([
                'design_id'  => $designId,
                'media_type' => 'image',
                'media_path' => null,
                'order'      => 1,
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach (['es', 'en', 'fr'] as $lang) {
                DB::table('design_item_translations')->insert([
                    'design_item_id' => $itemId,
                    'lang'           => $lang,
                    'title'          => null,
                    'subtitle'       => null,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('designs')->where('key', 'doctor_section_bg')->delete();

        if (Schema::hasColumn('designs', 'order')) {
            Schema::table('designs', function (Blueprint $table) {
                $table->dropColumn('order');
            });
        }
    }
};

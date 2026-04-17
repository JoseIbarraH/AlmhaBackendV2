<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Group (Contact) - Reordered: Email, Phone, WhatsApp, Location
            ['key' => 'email', 'value' => 'contacto@almha.com', 'group' => 'general'],
            ['key' => 'phone', 'value' => '+57 000 000 0000', 'group' => 'general'],
            ['key' => 'whatsapp', 'value' => [
                'number' => '+57 000 000 0000',
                'message' => 'Hola, quisiera más información.',
                'show_button' => true,
                'open_new_tab' => true
            ], 'group' => 'general'],
            ['key' => 'location', 'value' => 'Calle Principal #123, Ciudad', 'group' => 'general'],

            // Social Group
            ['key' => 'facebook', 'value' => 'https://facebook.com/almha', 'group' => 'social'],
            ['key' => 'instagram', 'value' => 'https://instagram.com/almha', 'group' => 'social'],
            ['key' => 'tiktok', 'value' => 'https://tiktok.com/@almha', 'group' => 'social'],
            ['key' => 'twitter', 'value' => 'https://twitter.com/almha', 'group' => 'social'],
            ['key' => 'linkedin', 'value' => 'https://linkedin.com/company/almha', 'group' => 'social'],
            ['key' => 'threads', 'value' => 'https://threads.net/@almha', 'group' => 'social'],

            // System Group
            ['key' => 'is_maintenance_mode', 'value' => '0', 'group' => 'system'],
        ];

        foreach ($settings as $setting) {
            \Src\Admin\Settings\Infrastructure\Models\EloquentSettingModel::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'group' => $setting['group']]
            );
        }
    }
}

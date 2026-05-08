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

            // About — Misión y Visión (multi-idioma; fr se regenera automáticamente al editar desde admin)
            ['key' => 'about_mission', 'value' => [
                'es' => [
                    'title' => 'Nuestra Misión',
                    'description' => 'Proporcionar servicios de cirugía estética de clase mundial, combinando tecnología de vanguardia con un enfoque humano y personalizado para cada paciente.',
                ],
                'en' => [
                    'title' => 'Our Mission',
                    'description' => 'To provide world-class aesthetic surgery services, combining cutting-edge technology with a human, personalized approach for every patient.',
                ],
                'fr' => [
                    'title' => 'Notre Mission',
                    'description' => 'Offrir des services de chirurgie esthétique de classe mondiale, en alliant une technologie de pointe à une approche humaine et personnalisée pour chaque patient.',
                ],
            ], 'group' => 'general'],
            ['key' => 'about_vision', 'value' => [
                'es' => [
                    'title' => 'Nuestra Visión',
                    'description' => 'Ser la clínica de referencia en cirugía estética, reconocida por nuestra excelencia médica, innovación constante y el compromiso genuino con el bienestar de nuestros pacientes.',
                ],
                'en' => [
                    'title' => 'Our Vision',
                    'description' => 'To be the reference clinic in aesthetic surgery, recognized for our medical excellence, constant innovation, and genuine commitment to our patients\' wellbeing.',
                ],
                'fr' => [
                    'title' => 'Notre Vision',
                    'description' => 'Être la clinique de référence en chirurgie esthétique, reconnue pour notre excellence médicale, notre innovation constante et notre engagement sincère envers le bien-être de nos patients.',
                ],
            ], 'group' => 'general'],

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

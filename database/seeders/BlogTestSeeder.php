<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Admin\Blog\Infrastructure\Models\BlogCategoryEloquentModel;
use Src\Admin\Blog\Infrastructure\Models\BlogCategoryTranslationEloquentModel;
use Src\Admin\Blog\Infrastructure\Models\BlogEloquentModel;
use Src\Admin\Blog\Infrastructure\Models\BlogTranslationEloquentModel;

class BlogTestSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear categoría de prueba
        $category = BlogCategoryEloquentModel::create([
            'code' => 'tech',
        ]);

        BlogCategoryTranslationEloquentModel::create([
            'category_id' => $category->id,
            'lang' => 'es',
            'title' => 'Tecnología',
        ]);

        BlogCategoryTranslationEloquentModel::create([
            'category_id' => $category->id,
            'lang' => 'en',
            'title' => 'Technology',
        ]);

        // 2. Crear blog de prueba
        $blog = BlogEloquentModel::create([
            'category_code' => 'tech',
            'status' => 'published',
            'writer' => 'Admin',
            'views' => 0,
            'published_at' => now(),
        ]);

        BlogTranslationEloquentModel::create([
            'blog_id' => $blog->id,
            'lang' => 'es',
            'title' => 'Introducción a la inteligencia artificial',
            'slug' => 'introduccion-a-la-inteligencia-artificial',
            'content' => '<p>La inteligencia artificial (IA) es una rama de la informática que busca crear sistemas capaces de realizar tareas que normalmente requieren inteligencia humana.</p>',
        ]);

        BlogTranslationEloquentModel::create([
            'blog_id' => $blog->id,
            'lang' => 'en',
            'title' => 'Introduction to artificial intelligence',
            'slug' => 'introduction-to-artificial-intelligence',
            'content' => '<p>Artificial intelligence (AI) is a branch of computer science that seeks to create systems capable of performing tasks that normally require human intelligence.</p>',
        ]);

        // 3. Segundo blog de prueba
        $blog2 = BlogEloquentModel::create([
            'category_code' => 'tech',
            'status' => 'draft',
            'writer' => 'Admin',
            'views' => 0,
        ]);

        BlogTranslationEloquentModel::create([
            'blog_id' => $blog2->id,
            'lang' => 'es',
            'title' => 'Los mejores frameworks de PHP en 2026',
            'slug' => 'los-mejores-frameworks-de-php-en-2026',
            'content' => '<p>Laravel sigue siendo el framework más popular de PHP, seguido por Symfony y otros.</p>',
        ]);

        BlogTranslationEloquentModel::create([
            'blog_id' => $blog2->id,
            'lang' => 'en',
            'title' => 'The best PHP frameworks in 2026',
            'slug' => 'the-best-php-frameworks-in-2026',
            'content' => '<p>Laravel remains the most popular PHP framework, followed by Symfony and others.</p>',
        ]);

        $this->command->info('✅ Blogs de prueba creados exitosamente');
    }
}

<?php

namespace Src\Admin\Blog\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class BlogTranslationEloquentModel extends Model
{
    use HasSlug;

    protected $table = 'blog_translations';

    protected $fillable = [
        'blog_id',
        'lang',
        'title',
        'slug',
        'content'
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /**
     * Boot: garantizar que el slug se genera si HasSlug no lo hace
     */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->slug) && !empty($model->title)) {
                $slug = Str::slug($model->title);

                // Asegurar unicidad
                $originalSlug = $slug;
                $counter = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $counter++;
                }

                $model->slug = $slug;
            }
        });
    }

    public function blog()
    {
        return $this->belongsTo(BlogEloquentModel::class, 'blog_id');
    }
}

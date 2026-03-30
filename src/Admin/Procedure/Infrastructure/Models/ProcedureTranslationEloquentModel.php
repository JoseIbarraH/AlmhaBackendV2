<?php

namespace Src\Admin\Procedure\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\SlugOptions; 
use Spatie\Sluggable\HasSlug;
use Illuminate\Support\Str;

class ProcedureTranslationEloquentModel extends Model
{
    use HasSlug;

    protected $table = 'procedure_translations';

    protected $fillable = [
        'procedure_id',
        'lang',
        'slug',
        'title',
        'subtitle'
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

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

    public function procedure()
    {
        return $this->belongsTo(ProcedureEloquentModel::class, 'procedure_id');
    }
}

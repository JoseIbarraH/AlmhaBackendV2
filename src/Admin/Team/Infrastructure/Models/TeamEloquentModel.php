<?php

namespace Src\Admin\Team\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class TeamEloquentModel extends Model
{
    use SoftDeletes, HasSlug;

    protected $table = 'teams';

    protected $fillable = [
        'user_id',
        'slug',
        'name',
        'status',
        'image'
    ];

    public function translations()
    {
        return $this->hasMany(TeamTranslationEloquentModel::class, 'team_id');
    }

    public function images()
    {
        return $this->hasMany(TeamImageEloquentModel::class, 'team_id');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
        ->generateSlugsFrom('name')
        ->saveSlugsTo('slug')
        ->doNotGenerateSlugsOnUpdate();

    }

}

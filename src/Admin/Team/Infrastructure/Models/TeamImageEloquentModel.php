<?php

namespace Src\Admin\Team\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class TeamImageEloquentModel extends Model
{
    protected $table = 'team_images';

    protected $fillable = [
        'team_id',
        'path',
        'order'
    ];

    public function team()
    {
        return $this->belongsTo(TeamEloquentModel::class, 'team_id');
    }

    public function translations()
    {
        return $this->hasMany(TeamImageTranslationEloquentModel::class, 'team_image_id');
    }
}

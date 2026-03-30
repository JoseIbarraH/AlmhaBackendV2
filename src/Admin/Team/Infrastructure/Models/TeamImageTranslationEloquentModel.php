<?php

namespace Src\Admin\Team\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class TeamImageTranslationEloquentModel extends Model
{
    protected $table = 'team_image_translations';

    protected $fillable = [
        'team_image_id',
        'lang',
        'description'
    ];

    public function teamImage()
    {
        return $this->belongsTo(TeamImageEloquentModel::class, 'team_image_id');
    }
}

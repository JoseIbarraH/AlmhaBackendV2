<?php

namespace Src\Admin\Team\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class TeamTranslationEloquentModel extends Model
{
    protected $table = 'team_translations';

    protected $fillable = [
        'team_id',
        'lang',
        'specialization',
        'description',
        'biography'
    ];

    public function team()
    {
        return $this->belongsTo(TeamEloquentModel::class, 'team_id');
    }

}

<?php

namespace Src\Admin\Design\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class EloquentDesignItemTranslationModel extends Model
{
    protected $table = 'design_item_translations';

    protected $fillable = [
        'design_item_id',
        'lang',
        'title',
        'subtitle'
    ];
}

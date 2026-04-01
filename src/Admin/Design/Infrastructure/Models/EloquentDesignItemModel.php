<?php

namespace Src\Admin\Design\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class EloquentDesignItemModel extends Model
{
    protected $table = 'design_items';

    protected $fillable = [
        'design_id',
        'media_type',
        'media_path',
        'order',
        'status'
    ];

    public function translations()
    {
        return $this->hasMany(EloquentDesignItemTranslationModel::class, 'design_item_id', 'id');
    }

    public function design()
    {
        return $this->belongsTo(EloquentDesignModel::class, 'design_id', 'id');
    }
}

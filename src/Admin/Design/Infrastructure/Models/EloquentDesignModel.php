<?php

namespace Src\Admin\Design\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class EloquentDesignModel extends Model
{
    protected $table = 'designs';

    protected $fillable = [
        'key',
        'display_mode',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(EloquentDesignItemModel::class, 'design_id', 'id')->orderBy('order', 'asc');
    }
}

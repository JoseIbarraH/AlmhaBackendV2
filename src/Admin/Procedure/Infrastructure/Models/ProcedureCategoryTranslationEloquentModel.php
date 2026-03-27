<?php

namespace Src\Admin\Procedure\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ProcedureCategoryTranslationEloquentModel extends Model
{
    protected $table = 'procedure_categories_translations';

    protected $fillable = [
        'category_id',
        'lang',
        'title'
    ];

    public function category()
    {
        return $this->belongsTo(ProcedureCategoryEloquentModel::class, 'category_id');
    }
}

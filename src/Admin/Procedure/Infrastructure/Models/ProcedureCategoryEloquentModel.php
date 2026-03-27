<?php

namespace Src\Admin\Procedure\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ProcedureCategoryEloquentModel extends Model
{
    protected $table = 'procedure_categories';

    protected $fillable = [
        'code'
    ];

    public function translations()
    {
        return $this->hasMany(ProcedureCategoryTranslationEloquentModel::class, 'category_id');
    }
}

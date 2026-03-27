<?php

namespace Src\Admin\Procedure\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ProcedureSectionTranslationEloquentModel extends Model
{
    protected $table = 'procedure_section_translations';

    protected $fillable = [
        'procedure_section_id',
        'lang',
        'title',
        'content_one',
        'content_two'
    ];

    public function section()
    {
        return $this->belongsTo(ProcedureSectionEloquentModel::class, 'procedure_section_id');
    }
}

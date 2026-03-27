<?php

namespace Src\Admin\Procedure\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ProcedureSectionEloquentModel extends Model
{
    protected $table = 'procedure_sections';

    protected $fillable = [
        'procedure_id',
        'type',
        'image',
        'order'
    ];

    public function procedure()
    {
        return $this->belongsTo(ProcedureEloquentModel::class, 'procedure_id');
    }

    public function translations()
    {
        return $this->hasMany(ProcedureSectionTranslationEloquentModel::class, 'procedure_section_id');
    }
}

<?php

namespace Src\Admin\Procedure\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ProcedurePostoperativeInstructionTranslationEloquentModel extends Model
{
    protected $table = 'procedure_postoperative_instruction_translations';

    protected $fillable = [
        'procedure_postoperative_instruction_id',
        'lang',
        'content'
    ];

    public function instruction()
    {
        return $this->belongsTo(ProcedurePostoperativeInstructionEloquentModel::class, 'procedure_postoperative_instruction_id');
    }
}

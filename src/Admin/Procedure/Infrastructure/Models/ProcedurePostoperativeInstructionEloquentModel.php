<?php

namespace Src\Admin\Procedure\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ProcedurePostoperativeInstructionEloquentModel extends Model
{
    protected $table = 'procedure_postoperative_instructions';

    protected $fillable = [
        'procedure_id',
        'type',
        'order'
    ];

    public function procedure()
    {
        return $this->belongsTo(ProcedureEloquentModel::class, 'procedure_id');
    }

    public function translations()
    {
        return $this->hasMany(ProcedurePostoperativeInstructionTranslationEloquentModel::class, 'procedure_postoperative_instruction_id');
    }
}

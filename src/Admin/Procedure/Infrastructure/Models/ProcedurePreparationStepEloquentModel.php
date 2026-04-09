<?php

namespace Src\Admin\Procedure\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ProcedurePreparationStepEloquentModel extends Model
{
    protected $table = 'procedure_preparation_steps';

    protected $fillable = [
        'procedure_id',
        'order'
    ];

    protected $casts = [
        'order' => 'integer'
    ];

    public function procedure()
    {
        return $this->belongsTo(ProcedureEloquentModel::class, 'procedure_id');
    }

    public function translations()
    {
        return $this->hasMany(ProcedurePreparationStepTranslationEloquentModel::class, 'procedure_preparation_step_id');
    }
}

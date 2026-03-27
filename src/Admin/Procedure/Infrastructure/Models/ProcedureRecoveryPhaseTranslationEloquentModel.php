<?php

namespace Src\Admin\Procedure\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ProcedureRecoveryPhaseTranslationEloquentModel extends Model
{
    protected $table = 'procedure_recovery_phase_translations';

    protected $fillable = [
        'procedure_recovery_phase_id',
        'lang',
        'period',
        'title',
        'description'
    ];

    public function recoveryPhase()
    {
        return $this->belongsTo(ProcedureRecoveryPhaseEloquentModel::class, 'procedure_recovery_phase_id');
    }
}

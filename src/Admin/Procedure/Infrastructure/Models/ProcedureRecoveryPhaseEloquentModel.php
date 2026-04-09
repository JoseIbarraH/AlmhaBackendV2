<?php

namespace Src\Admin\Procedure\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ProcedureRecoveryPhaseEloquentModel extends Model
{
    protected $table = 'procedure_recovery_phases';

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
        return $this->hasMany(ProcedureRecoveryPhaseTranslationEloquentModel::class, 'procedure_recovery_phase_id');
    }
}

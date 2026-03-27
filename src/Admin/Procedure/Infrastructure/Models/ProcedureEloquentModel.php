<?php

namespace Src\Admin\Procedure\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcedureEloquentModel extends Model
{
    use SoftDeletes;

    protected $table = 'procedures';

    protected $fillable = [
        'user_id',
        'image',
        'category_code',
        'status',
        'views'
    ];

    public function translations()
    {
        return $this->hasMany(ProcedureTranslationEloquentModel::class, 'procedure_id');
    }

    public function sections()
    {
        return $this->hasMany(ProcedureSectionEloquentModel::class, 'procedure_id');
    }

    public function faqs()
    {
        return $this->hasMany(ProcedureFaqEloquentModel::class, 'procedure_id');
    }

    public function postoperativeInstructions()
    {
        return $this->hasMany(ProcedurePostoperativeInstructionEloquentModel::class, 'procedure_id');
    }

    public function preparationSteps()
    {
        return $this->hasMany(ProcedurePreparationStepEloquentModel::class, 'procedure_id');
    }

    public function recoveryPhases()
    {
        return $this->hasMany(ProcedureRecoveryPhaseEloquentModel::class, 'procedure_id');
    }

    public function gallery()
    {
        return $this->hasMany(ProcedureResultGalleryEloquentModel::class, 'procedure_id');
    }
}

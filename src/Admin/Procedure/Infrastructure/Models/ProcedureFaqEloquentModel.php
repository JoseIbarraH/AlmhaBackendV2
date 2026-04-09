<?php

namespace Src\Admin\Procedure\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ProcedureFaqEloquentModel extends Model
{
    protected $table = 'procedure_faqs';

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
        return $this->hasMany(ProcedureFaqTranslationEloquentModel::class, 'procedure_faq_id');
    }
}

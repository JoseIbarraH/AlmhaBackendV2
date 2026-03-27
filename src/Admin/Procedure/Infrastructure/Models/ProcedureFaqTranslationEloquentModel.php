<?php

namespace Src\Admin\Procedure\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ProcedureFaqTranslationEloquentModel extends Model
{
    protected $table = 'procedure_faq_translations';

    protected $fillable = [
        'procedure_faq_id',
        'lang',
        'question',
        'answer'
    ];

    public function faq()
    {
        return $this->belongsTo(ProcedureFaqEloquentModel::class, 'procedure_faq_id');
    }
}

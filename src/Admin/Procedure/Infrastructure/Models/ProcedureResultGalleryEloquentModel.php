<?php

namespace Src\Admin\Procedure\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ProcedureResultGalleryEloquentModel extends Model
{
    protected $table = 'procedure_result_galleries';

    protected $fillable = [
        'procedure_id',
        'path',
        'type',
        'pair_id',
        'order'
    ];

    public function procedure()
    {
        return $this->belongsTo(ProcedureEloquentModel::class, 'procedure_id');
    }
}

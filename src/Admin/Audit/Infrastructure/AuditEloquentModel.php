<?php

declare(strict_types=1);

namespace Src\Admin\Audit\Infrastructure;

use Illuminate\Database\Eloquent\Model;

final class AuditEloquentModel extends Model
{
    protected $table = 'audits';

    protected $fillable = [
        'user_id',
        'action',
        'method',
        'url',
        'payload',
        'response_status',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'payload' => 'array'
    ];
}

<?php

declare(strict_types=1);

namespace Src\Landing\Subscription\Infrastructure\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class SubscriberEloquentModel extends Model
{
    use HasUuids;

    protected $table = 'subscribers';

    protected $fillable = [
        'id',
        'email',
        'token',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];
}

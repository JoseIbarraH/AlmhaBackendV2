<?php

namespace Src\Admin\Settings\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class EloquentSettingModel extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    protected $casts = [
        'value' => 'json',
    ];
}

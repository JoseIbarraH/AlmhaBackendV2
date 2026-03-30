<?php

declare(strict_types=1);

namespace Src\Admin\Role\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

final class RoleTranslationModel extends Model
{
    protected $table = 'role_translations';

    protected $fillable = [
        'role_id',
        'lang',
        'title'
    ];
}

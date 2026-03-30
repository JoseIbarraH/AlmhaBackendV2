<?php

declare(strict_types=1);

namespace Src\Admin\Role\Infrastructure\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CustomRole extends SpatieRole
{
    public function translations(): HasMany
    {
        return $this->hasMany(RoleTranslationModel::class, 'role_id');
    }
}

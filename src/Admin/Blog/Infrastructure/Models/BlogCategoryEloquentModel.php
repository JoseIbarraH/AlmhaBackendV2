<?php

namespace Src\Admin\Blog\Infrastructure\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int    $id
 * @property string $code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property Collection<int, BlogCategoryTranslationEloquentModel> $translations
 */
class BlogCategoryEloquentModel extends Model
{
    protected $table = 'blog_categories';

    protected $fillable = [
        'code'
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(BlogCategoryTranslationEloquentModel::class, 'category_id');
    }
}

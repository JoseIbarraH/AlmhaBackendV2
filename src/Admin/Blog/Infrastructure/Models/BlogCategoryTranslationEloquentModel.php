<?php

namespace Src\Admin\Blog\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property int    $category_id
 * @property string $lang
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class BlogCategoryTranslationEloquentModel extends Model
{
    protected $table = 'blog_category_translations';

    protected $fillable = [
        'category_id',
        'lang',
        'title'
    ];
}

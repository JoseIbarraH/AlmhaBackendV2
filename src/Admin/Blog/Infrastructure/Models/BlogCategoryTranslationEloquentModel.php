<?php

namespace Src\Admin\Blog\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class BlogCategoryTranslationEloquentModel extends Model
{
    protected $table = 'blog_category_translations';

    protected $fillable = [
        'category_id',
        'lang',
        'title'
    ];
}

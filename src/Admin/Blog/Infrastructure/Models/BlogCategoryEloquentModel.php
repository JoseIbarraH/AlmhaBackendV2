<?php

namespace Src\Admin\Blog\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class BlogCategoryEloquentModel extends Model
{
    protected $table = 'blog_categories';

    protected $fillable = [
        'code'
    ];

    public function translations()
    {
        return $this->hasMany(BlogCategoryTranslationEloquentModel::class, 'category_id');
    }
}

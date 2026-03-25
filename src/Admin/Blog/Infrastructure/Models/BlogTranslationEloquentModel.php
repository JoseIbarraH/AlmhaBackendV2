<?php

namespace Src\Admin\Blog\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class BlogTranslationEloquentModel extends Model
{
    protected $table = 'blog_translations';

    protected $fillable = [
        'blog_id',
        'lang',
        'title',
        'content'
    ];

    public function blog()
    {
        return $this->belongsTo(BlogEloquentModel::class, 'blog_id');
    }
}

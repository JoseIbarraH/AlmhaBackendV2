<?php

namespace Src\Admin\Blog\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogEloquentModel extends Model
{
    use SoftDeletes;
    
    protected $table = 'blogs';

    protected $fillable = [
        'user_id',
        'image',
        'category_code',
        'writer',
        'views',
        'status',
        'published_at',
        'notification_sent_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'notification_sent_at' => 'datetime',
    ];

    public function translations()
    {
        return $this->hasMany(BlogTranslationEloquentModel::class, 'blog_id');
    }
}

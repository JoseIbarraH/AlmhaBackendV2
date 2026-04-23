<?php

namespace Src\Admin\Blog\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $user_id
 * @property string|null $image
 * @property string|null $category_code
 * @property string|null $writer
 * @property int $views
 * @property string $status
 * @property Carbon|null $published_at
 * @property Carbon|null $notification_sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property \Illuminate\Database\Eloquent\Collection|BlogTranslationEloquentModel[] $translations
 *
 * @method static \Illuminate\Database\Eloquent\Builder|BlogEloquentModel query()
 * @method static \Illuminate\Database\Eloquent\Builder|BlogEloquentModel where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static BlogEloquentModel|null find($id, $columns = ['*'])
 *
 * @mixin \Eloquent
 */
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

    public function translations(): HasMany
    {
        return $this->hasMany(BlogTranslationEloquentModel::class, 'blog_id');
    }
}

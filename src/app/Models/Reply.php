<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Reply extends Model
{
    use HasFactory;
    // Определяем атрибуты, которые могут быть массово присвоены
    protected $fillable = [
        'comment_id',
        'parent_id',
        'user_name',
        'avatar',
        'email',
        'home_page',
        'text',
        'file_path',
    ];

    /**
     * Связь "ответ принадлежит комментарию".
     * Этот метод определяет, что каждый Reply принадлежит к определенному Comment через поле comment_id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }

    /**
     * Связь "ответ имеет родительский ответ".
     *Этот метод определяет, что каждый Reply может иметь один родительский ответ через поле parent_id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
        public function parent()
        {
            return $this->belongsTo(Reply::class, 'parent_id');
        }

    /**
     * Связь "ответ имеет дочерние ответы".
     *Связь с дочерними ответами. Этот метод устанавливает, что каждый Reply может иметь несколько дочерних ответов через поле parent_id
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
        public function children()
        {
            return $this->hasMany(Reply::class, 'parent_id');
        }

        public function getFileUrlAttribute()
        {
            return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
        }

}

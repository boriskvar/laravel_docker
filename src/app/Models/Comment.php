<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_id',
        'user_name',
        'avatar',
        'email',
        'home_page',
        'captcha',
        'text',
        'rating',
        'quote',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Set the comment's text, stripping unwanted tags.
     *
     * @param  string  $value
     * @return void
     */
    public function setTextAttribute($value)
    {
        $this->attributes['text'] = strip_tags($value, '<a><code><i><strong>');
    }

    /**
     * Set the comment's user name, removing non-alphanumeric characters.
     *
     * @param  string  $value
     * @return void
     */
    public function setUserNameAttribute($value)
    {
        $this->attributes['user_name'] = preg_replace('/[^a-zA-Z0-9]/', '', $value);
    }

    /**
     * Set the comment's home page URL.
     *
     * @param  string  $value
     * @return void
     */
    public function setHomePageAttribute($value)
    {
        $this->attributes['home_page'] = filter_var($value, FILTER_VALIDATE_URL) ? $value : null;
    }

    /**
     * Set the comment's email address.
     *
     * @param  string  $value
     * @return void
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
    }

    /**
     * Get the comment's replies.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('replies'); //Добавлено ->with('replies'), чтобы рекурсивно загружать все вложенные ответы.
    }

    /**
     * Get the parent comment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }
}

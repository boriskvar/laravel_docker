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

        'user_name',
        'avatar',
        'email',
        'home_page',
        'captcha',
        'text',
        'rating',
        'quote',
        'file_path',
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
     * Очищаем текст от нежелательных тегов HTML
     * @param  string  $value
     * @return void
     */

     public function setTextAttribute($value)
    {
        $this->attributes['text'] = strip_tags($value, '<a><code><i><strong>');
    }

    /**
     * Set the comment's user name, removing non-alphanumeric characters.
     * Удаляет неалфавитные символы из имени пользователя
     * @param  string  $value
     * @return void
     */
    public function setUserNameAttribute($value)
    {
        $this->attributes['user_name'] = preg_replace('/[^a-zA-Z0-9]/', '', $value);
    }

    /**
     * Set the comment's home page URL.
     * Проверяет, является ли URL допустимым
     * @param  string  $value
     * @return void
     */
    public function setHomePageAttribute($value)
    {
        $this->attributes['home_page'] = filter_var($value, FILTER_VALIDATE_URL) ? $value : null;
    }

    /**
     * Set the comment's email address.
     * Проверяет, является ли email допустимым
     * @param  string  $value
     * @return void
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
    }

    /**
     * Get the comment's replies.
     * устанавливает связь один ко многим между комментариями и ответами
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(Reply::class, 'comment_id');
    }


}



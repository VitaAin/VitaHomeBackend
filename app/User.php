<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Hash;
use App\Article;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'confirm_code'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function setPasswordAttribute($pwd)
    {
        return $this->attributes['password'] = Hash::make($pwd);
    }

    public function likes()
    {
        return $this->belongsToMany(Article::class, 'likes')
            ->withTimestamps();
    }

    public function followers()
    {
        return $this->belongsToMany(self::class, 'followers', 'follower_id', 'followed_id')
            ->withTimestamps();
    }

    public function followedUsers()
    {
        return $this->belongsToMany(self::class, 'followers', 'followed_id', 'follower_id')
            ->withTimestamps();
    }

    public function messages()
    {

    }

    public function likeThis($article)
    {
        return $this->likes()->toggle($article);
    }

    public function isLikeThis($article)
    {
        return $this->likes()
            ->where('article_id', $article)
            ->count();
    }

    public function followThis($user)
    {
        return $this->followers()->toggle($user);
    }
}

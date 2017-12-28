<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Hash;
use App\Article;

class User extends Authenticatable implements JWTSubject
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

    /**
     * 将属性转换为常见的数据类型
     * @var array
     */
    protected $casts = [
        'is_banned' => 'boolean',
//        'is_confirmed' => 'boolean'
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

    public function images()
    {
        return $this->belongsToMany(Image::class, 'images','user_id','image_id')
            ->withTimestamps();
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

    public function followThisUser($user)
    {
        return $this->followers()->toggle($user);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        // TODO: Implement getJWTIdentifier() method.
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        // TODO: Implement getJWTCustomClaims() method.
        return [];
    }
}

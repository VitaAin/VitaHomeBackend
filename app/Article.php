<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    /**
     * 可以被批量赋值的属性
     * @var array
     */
    protected $fillable = [
        'title', 'body', 'user_id', 'article_url', 'category_id'
    ];

    /**
     * 将属性转换为常见的数据类型
     * @var array
     */
    protected $casts = [
        'close_comment' => 'boolean',
        'is_public' => 'boolean',
        'is_top' => 'boolean',
        'is_excellent' => 'boolean'
    ];

    /**
     * 获得此文章的标签
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function images()
    {
        return $this->belongsToMany(ArticleImage::class)->withTimestamps();
    }

    /**
     * 获得此文章的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获得此文章的分类
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * 获得此文章的所有评论
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * 获得此文章的所有点赞用户
     */
    public function likes()
    {
        return $this->belongsToMany(User::class, 'likes')->withTimestamps();
    }

    public function scopeIsPublic($query)
    {
        return $query->where('is_public', '1');
    }
}

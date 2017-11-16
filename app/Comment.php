<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'user_id', 'body', 'commentable_id', 'commentable_type', 'parent_id'
    ];

    protected $casts = [
        'is_public' => 'boolean'
    ];

    /**
     * 获得拥有此评论的模型
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function childComments()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

}

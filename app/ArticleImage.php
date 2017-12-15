<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArticleImage extends Model
{
    protected $fillable = [
        'type','article_id','uid','name', 'url','size'
    ];

    public function articles()
    {
        return $this->belongsToMany(Article::class)->withTimestamps();
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        "name", "articles_count"
    ];

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}

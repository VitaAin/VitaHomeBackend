<?php

namespace App\Repositories;

use App\Article;
use Illuminate\Support\Facades\Cache;

class ArticlesRepository
{
    public function getArticles($page, $request)
    {
        if (empty($request->tag)) {
            return Cache::tags('articles')->remember('articles' . $page, $minutes = 10,
                function () {
                    return Article::isPublic()->with('user', 'tags', 'category')->latest('created_at')->paginate(10);
                });
        } else {
            return Cache::tags('articles')->remember('articles' . $page . $request->tag, $minutes = 10,
                function () use ($request) {
                    return Article::isPublic()->whereHas('tags',
                        function ($query) use ($request) {
                            $query->where('name', $request->tag);
                        })->with('user', 'tags', 'category')->latest('created_at')->paginate(10);
                });
        }
    }

    public function getArticle($id)
    {
        $article = Article::where('id', $id);
        $article->increment('view_count', 1);
        return $article->with('user', 'tags')->first();
    }

    public function byId($id)
    {
        return Article::find($id);
    }
}

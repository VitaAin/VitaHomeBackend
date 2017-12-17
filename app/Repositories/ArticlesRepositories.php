<?php

namespace App\Repositories;

use App\ArticleImage;
use App\Category;
use App\Tag;
use Cache;
use App\Article;

class ArticlesRepository
{
    public function getArticles($page, $request)
    {
        if (empty($request->tag)) {
            return Cache::tags('articles')
                ->remember('articles' . $page, $minutes = 10,
                    function () {
                        return Article::isPublic()
                            ->with('user', 'tags', 'category')
                            ->latest('created_at')
                            ->paginate(10);
                    });
        } else {
            return Cache::tags('articles')
                ->remember('articles' . $page . $request->tag, $minutes = 10,
                    function () use ($request) {
                        return Article::isPublic()->whereHas('tags',
                            function ($query) use ($request) {
                                $query->where('name', $request->tag);
                            })->with('user', 'tags', 'category')
                            ->latest('created_at')
                            ->paginate(10);
                    });
        }
    }

    public function getArticle($id)
    {
        $article = Article::where('id', $id);
        $article->increment('view_count', 1);
        return $article->with('user', 'tags', 'category')->first();
    }

    public function findArticleById($id)
    {
        return Article::find($id);
    }

    public function createCategory($category)
    {
        $categoryFind = Category::find($category);
        if (empty($categoryFind)) {
            $newCategory = Category::create([
                'name' => $category,
                'articles_count' => 1
            ]);
            return $newCategory->id;
        } else {
            $categoryFind->increment('articles_count');
            return $categoryFind->id;
        }

    }

    public function createTags($tags)
    {
        return collect($tags)->map(function ($tag) {
            if (is_numeric($tag)) {
                Tag::find($tag)->increment('articles_count');
                return (int)$tag;
            }
            $newTag = Tag::create([
                'name' => $tag,
                'articles_count' => 1
            ]);
            return $newTag->id;
        })->toArray();
    }

    public function editTags($tags, $articleId)
    {
        $oldTags = Article::find($articleId)
            ->tags
            ->pluck('id')
            ->toArray();
        if (is_null($tags)) {
            $tags = [];
        }
        $reduceTags = array_diff($oldTags, $tags);
        $addTags = array_diff($tags, $oldTags);

        foreach ($reduceTags as $reduceTag) {
            $tag = Tag::where('id', $reduceTag);
            $tagCount = $tag->count();
            if ($tagCount > 1) {
                \DB::table('article_tag')
                    ->where('tag_id', $reduceTag)
                    ->where('article_id', $articleId)
                    ->delete();
                $tag->decrement('count', 1);
            } else {
                $tag->delete();
            }
        }

        if (is_null($addTags)) {
            return false;
        } else {
            return $addTags;
        }
    }

    public function create(array $attributes)
    {
        return Article::create($attributes);
    }

    public function createImages($article, array $images)
    {
        if (empty($images)) {
            return [];
        }
        foreach ($images as $image) {
//            if ($image->article_id) continue;
            $image->article_id = $article->id;
        }
        return collect($images)->map(function ($image) {
            $newImage = ArticleImage::create($image);
            return $newImage->id;
        })->toArray();
    }
}

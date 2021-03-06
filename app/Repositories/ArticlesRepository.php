<?php

namespace App\Repositories;

use App\Image;
use App\Category;
use App\Tag;
use Auth;
use Cache;
use App\Article;
use DB;
use Log;

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
                                $query->where('name', $request->get('tag'));
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

    public function editTags($article /*$articleId*/, array $tags)
    {
        $articleId = $article->id;
        $oldTags = Article::find($articleId)
            ->tags
            ->pluck('id')
            ->toArray();
        if (is_null($tags)) {
            $tags = [];
        }
        Log::info(\GuzzleHttp\json_encode($tags));
        $reduceTags = array_diff($oldTags, $tags);
        $addTags = array_diff($tags, $oldTags);

        foreach ($reduceTags as $reduceTag) {
            $tag = Tag::where('id', $reduceTag);
            $tagCount = $tag->count();
            if ($tagCount > 1) {
                DB::table('article_tag')
                    ->where('tag_id', $reduceTag)
                    ->where('article_id', $articleId)
                    ->delete();
                $tag->decrement('articles_count', 1);
            }
        }

//        if (is_null($addTags)) {
//            return false;
//        } else {
//            return $addTags;
//        }
        if (!is_null($addTags)) {
            foreach ($addTags as $addTag) {
                if (is_numeric($addTag)) {
                    $article->tags()->attach($addTag);
                    Tag::where('id', $addTag)->increment('articles_count', 1);
                } else {
                    $article->tags()->create([
                        'name' => $addTag,
                        'article_count' => 1
                    ]);
                }
            }
        }
    }

    public function create(array $attributes)
    {
        return Article::create($attributes);
    }

    public function createImages($images)
    {
        if (empty($images)) {
            return [];
        }
        return collect($images)->map(function ($image) {
            Log::info('image:: ' . \GuzzleHttp\json_encode($image));
            if (empty($image['id'])) {
                $image['user_id'] = Auth::id();
                $newImage = Image::create($image);
                return $newImage->id;
            }
        })->toArray();
    }
}

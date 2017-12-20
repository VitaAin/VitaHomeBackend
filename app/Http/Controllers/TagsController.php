<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Tag;
use Cache;
use DB;

class TagsController extends Controller
{
    /**
     * action: GET, URI: /tags
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $tags = Cache::get('Tags_cache');
        if (empty($tag)) {
            $tags = DB::table('tags')
                ->select('id', 'name')
                ->get();
            Cache::put('Tags_cache', $tags, 10);
        }
        return $this->responseOk('OK', $tags);
    }

    public function hotTags()
    {
        $hotTags = Cache::get('hotTags_cache');
        if (empty($hotTags)) {
            $hotTags = Tag::where([])
                ->orderBy('articles_count', 'desc')
                ->take(30)
                ->get();
            Cache::put('hotTags_cache', $hotTags, 10);
        }
        return $this->responseOk('OK', $hotTags);
    }

    /**
     * action: POST, URI: /tags
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = [
            'name' => $request->get('name'),
            'articles_count' => 0
        ];
        $tag = Tag::create($data);
        $tag->description = $request->get('description');
        $tag->save();
        //TODO
//        $images = $this->articlesRepository->createImages($article->id, $request->get('images'));
//        Auth::user()->increment('articles_count');
//        Auth::user()->increment('images_count', count($images));
//        $article->increment('images_count', count($images));
//        $article->tags()->attach($tags);
//        Cache::tags('articles')->flush();

        return $this->responseOk('OK', $tag);
    }
}

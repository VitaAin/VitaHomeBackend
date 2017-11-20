<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Tag;
use Cache;

class TagsController extends Controller
{
    public function index()
    {
        $tags = Cache::get('Tags_cache');
        if (empty($tag)) {
            $tags = \DB::table('tags')
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
}
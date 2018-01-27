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
        $tags = DB::table('tags')
            ->get();
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
        $desc = $request->get('description');
        if (!empty($desc)) {
            $tag->description = $desc;
        }
        $tag->save();

        return $this->responseOk('OK', $tag);
    }
}

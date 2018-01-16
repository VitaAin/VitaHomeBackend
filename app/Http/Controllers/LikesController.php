<?php

namespace App\Http\Controllers;

use App\Article;
use App\Notifications\LikeArticleNotification;
use Illuminate\Http\Request;
use  Auth;

class LikesController extends Controller
{
    /**
     * LikesController constructor.
     */
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function isLike(Request $request)
    {
        $user = Auth::user();
        $liked = $user->isLikeThis($request->get('id'));
        $isLike = false;
        if ($liked == 1) {
            $isLike = true;
        } else {
            $isLike = false;
        }
        return $this->responseOk('OK', ['liked' => $isLike]);
    }

    public function likeThisArticle(Request $request)
    {
        $user = Auth::user();
        $article = Article::where('id', $request->get('id'))
            ->first();
        $liked = $user->likeThis($article->id);
        if (count($liked['detached']) > 0) {// cancel like
            $user->decrement('likes_count');
            $article->decrement('likes_count');
            return $this->responseOk('OK', ['liked' => false]);
        }
        $data = [
            'name' => $user->name,
            'user_id' => $user->id,
            'title' => $article->title,
            'title_id' => $article->id
        ];
        $article->user->notify(new LikeArticleNotification($data));
        $user->increment('likes_count');
        $article->increment('likes_count');
        return $this->responseOk('OK', ['liked' => true]);
    }
}

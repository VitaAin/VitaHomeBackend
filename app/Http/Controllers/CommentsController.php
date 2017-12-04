<?php

namespace App\Http\Controllers;

use App\Article;
use App\Notifications\CommentArticleNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Comment;
use Auth;

class CommentsController extends Controller
{

    /**
     * CommentsController constructor.
     */
    public function __construct()
    {
        $this->middleware('jwt.auth', [
            'only' => ['store']
        ]);
    }

    public function index($id)
    {
        $comments = Comment::where('commentable_id', $id)
            ->where('parent_id', 0)
            ->with(['user' => function ($query) {
                $query->select('id', 'name', 'avatar');
            }])
            ->get();
        return $this->responseOk('OK', $comments);
    }

    public function childComments($id)
    {
        $parentId = Request('parent_id');
        $newComments = [];
        $this->getChildComments($id, $parentId, $newComments);
        $this->responseOk('OK', $newComments);
    }

    private function getChildComments($id, $parentId, &$newComments)
    {
        $comments = Comment::where('commentable_id', $id)
            ->where('parent_id', $parentId)
            ->with(['user' => function ($query) {
                $query->select('id', 'name');
            }])
            ->get();

        if (empty($comments)) {
            return;
        }

        foreach ($comments as $comment) {
            $parent = Comment::where('id', $comment['parent_id'])
                ->first()->user()->first();
            $comment['parent_name'] = $parent->name;
            $comment['parent_user_id'] = $parent->id;
            $newComments[] = $comment;
            $this->getChildComments($id, $comment['id'], $newComments);
        }
    }

    public function store()
    {
        $user = Auth::user();
        $comment = Comment::create([
            'commentable_id' => request('article_id'),
            'commentable_type' => 'App\Article',
            'user_id' => $user->id,
            'parent_id' => request('parent_id'),
            'content' => request('content'),
        ]);

        if (empty($comment)) {
            return $this->responseError('Failed');
        }

        $user->increment('comments_count');
        $article = Article::where('id', request('article_id'));
        $article->increment('comments_count');
        $article->update([
            'last_comment_user_id' => $user->id,
            'last_comment_time' => Carbon::now()
        ]);
        $article = $article->first();

        $comment = Comment::where('id', $comment->id)
            ->with(['user' => function ($query) {
                $query->select('id', 'name', 'avatar');
            }])->first();

        $data = [
            'user_id' => $user->id,
            'name' => $user->name,
            'title' => $article->title,
            'title_id' => $article->id,
            'comment' => $comment->content
        ];
        if ($comment->parent_id == 0) {
            $comment->update([
                'floor' => $article->comments_count + 1
            ]);
            $article->user->notify(new CommentArticleNotification($data));
        } else {
            $parent = Comment::where('id', $comment->parent_id)
                ->first()->user()->first();
            $comment->update([
                'floor' => $parent->floor
            ]);
            $parent->increment('children_count');
            $comment->parent_name = $parent->name;
            $comment->parent_user_id = $parent->id;
            $parent->notify(new CommentArticleNotification($data));
        }

        return $this->responseOk('OK', $comment);
    }
}

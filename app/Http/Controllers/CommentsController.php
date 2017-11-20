<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Comment;

class CommentsController extends Controller
{

    /**
     * CommentsController constructor.
     */
    public function __construct()
    {

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
}

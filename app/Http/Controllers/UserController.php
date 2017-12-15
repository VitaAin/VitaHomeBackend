<?php

namespace App\Http\Controllers;

use App\Article;
use App\Comment;
use App\Transformer\CommentsTransformer;
use Illuminate\Http\Request;
use App\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Auth;
use Cache;
use Validator;

class UserController extends Controller
{
    /**
     * @var CommentTransformer
     */
    protected $commentsTransformer;

    /**
     * UserController constructor.
     * @param CommentsTransformer $commentsTransformer
     */
    public function __construct(CommentsTransformer $commentsTransformer)
    {
        $this->commentsTransformer = $commentsTransformer;
        $this->middleware('jwt.auth', [
            'except' => ['show']
        ]);
    }

    /**
     * action: GET, URI: /users/{id}
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = Cache::get('users_cache' . $id);
        if (empty($user)) {
            $user = User::findOrFail($id);
            Cache::put('users_cache' . $id, $user, 10);
        }
        return $this->responseOk('OK', $user);
    }

    public function userArticles($id)
    {
        $articles = Cache::get('user_articles' . $id);
        if (empty($articles)) {
            $articles = Article::where('user_id', $id)
                ->latest('created_at')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'comments_count' => $item->comments_count,
                        'likes_count' => $item->likes_count,
                        'created_at' => $item->created_at->toDateTimeString()
                    ];
                });
            Cache::put('user_articles' . $id, $articles, 10);
        }
        return $this->responseOk('OK', $articles);
    }

    public function userReplies($id)
    {
        $comments = Cache::get('user_replies' . $id);
        if (empty($comments)) {
            $comments = Comment::where('user_id', $id)
                ->with('commentable')
                ->latest('created_at')
                ->get()
                ->toArray();
            $comments = $this->commentsTransformer->transformCollection($comments);
            Cache::put('user_replies' . $id, $comments, 10);
        }
        return $this->responseOk('OK', $comments);
    }

    public function likeArticles($id)
    {
        if (empty($articles = Cache::get('user_likes_articles' . $id))) {
            $articles = User::find($id)
                ->likes
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'comments_count' => $item->comments_count,
                        'likes_count' => $item->likes_count,
                        'created_at' => $item->created_at->toDateTimeString()
                    ];
                });
            Cache::put('user_likes_articles' . $id, $articles, 10);
        }
        return $this->responseOk('OK', $articles);
    }

    public function followUsers($id)
    {
        if (empty($articles = Cache::get('user_follow_users' . $id))) {
            $articles = User::find($id)
                ->followers
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'avatar' => $item->avatar
                    ];
                });
            Cache::put('user_follow_users' . $id, $articles, 10);
        }
        return $this->responseOk('OK', $articles);
    }

    public function editPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|between:6,16|confirmed'
        ]);

        if ($validator->fails()) {
            return $this->responseError('Validate failed', $validator->errors()->toArray());
        }

        User::where('id', Auth::id())->update(['password' => request('password')]);
        return $this->responseOk('Change password successfully');
    }

    public function avatarUpload(Request $request)
    {
        $file = $request->file('file');
        $allowed_extensions = ['png', 'jpg', 'jpeg', 'gif'];
        $clientOriginalExt = $file->getClientOriginalExtension();
        if ($clientOriginalExt && !in_array($clientOriginalExt, $allowed_extensions)) {
            return $this->responseError('You may only upload png, jpg/jpeg or gif.');
        }
        $filename = md5(time()) . '.' . $clientOriginalExt;
        $file->move(public_path('image'), $filename);
        $avatar_image = env('APP_URL') . '/image/' . $filename;
        $user = Auth::user();
        $user->avatar = $avatar_image;
        $user->save();
        return $this->responseOk('Change avatar successfully', ['url' => $avatar_image]);
    }

    public function editUserInfo()
    {
        $data = [
            'real_name' => request('real_name'),
            'city' => request('city')
        ];
        User::where('id', Auth::id())->update($data);
        return $this->responseOk('Modify user info successfully', $data);
    }
}

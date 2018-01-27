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
use Log;
use DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

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
            'except' => ['show', 'about']
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
        if (empty($users = Cache::get('user_follow_users' . $id))) {
            $users = User::find($id)
                ->followers
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'avatar' => $item->avatar
                    ];
                });
            Cache::put('user_follow_users' . $id, $users, 10);
        }
        return $this->responseOk('OK', $users);
    }

    public function userImages($id)
    {
        $images = Cache::get('user_images' . $id);
        if (empty($images)) {
            $images = \App\Image::where('user_id', $id)
                ->latest('created_at')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'url' => $item->url,
                        'size' => $item->size,
                        'created_at' => $item->created_at->toDateTimeString()
                    ];
                });
            Cache::put('user_images' . $id, $images, 10);
        }
        return $this->responseOk('OK', $images);
    }

    public function userImageUpload(Request $request)
    {
        Log::info("---------------");
        Log::info($request->get('is_cover'));
        $file = $request->file('file');
        $allowed_extensions = ['png', 'jpg', 'jpeg', 'gif'];
        $clientOriginalExt = $file->getClientOriginalExtension();
        if ($clientOriginalExt && !in_array($clientOriginalExt, $allowed_extensions)) {
            return $this->responseError('You can only upload png, jpg/jpeg or gif.');
        }
        $filename = md5(time()) . '.' . $clientOriginalExt;
        $file->move(public_path('../storage/app/public/user_images/' . Auth::id()), $filename);
        // 要访问下面这个url（storage中的文件资源），需要为storage目录建立软连接到public/storage，执行：php artisan storage:link
        $imageUrl = env('APP_URL') . '/storage/user_images/' . Auth::id() . '/' . $filename;

        Auth::user()->increment('images_count', 1);

        return $this->responseOk('OK', ['url' => $imageUrl]);
    }

    public function userImageAdd(Request $request)
    {
        $image = $request->get('image');
        if (empty($image['id'])) {
            $image['user_id'] = Auth::id();
        }
        $fileName = array_last(explode('/', $image['url']));
        // TODO crop cover
        if ($image['is_cover']) {
            Image::configure(array('driver' => 'gd'));
            Image::make(public_path('../storage/app/public/user_images/' . Auth::id() . '/' . $fileName))
                ->fit(300, 200)->save();
        }
        $newImage = \App\Image::create($image);
        return $this->responseOk('OK', $newImage);
    }

    public function userImageDelete(Request $request)
    {
        $fileUrl = $request->get('url');
        $filename = array_last(explode('/', $fileUrl));
        $filePath = '/public/user_images/' . Auth::id() . '/' . $filename;
        Log::info('userImageDelete filePath: ' . $filePath);

        DB::table('images')
            ->where('url', $fileUrl)
            ->delete();
        $res = Storage::delete($filePath);
        if ($res) {
            Auth::user()->decrement('images_count', 1);

            return $this->responseOk('OK', $filePath);
        }
        return $this->responseError('Delete failed', ['url' => $fileUrl]);
    }

    public function editPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6|confirmed'
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
            'sex' => request('sex'),
            'phone' => request('phone'),
            'qq' => request('qq'),
            'city' => request('city'),
            'introduction' => request('introduction')
        ];
        User::where('id', Auth::id())->update($data);
        return $this->responseOk('Modify user info successfully', $data);
    }

    public function about()
    {
        $data = [
            'title' => 'Vita\'s Home',
            'alias' => '苍澜阁',
            'address' => 'http://www.vitain.top',
            'github' => 'https://github.com/VitaAin/VitaHome',
            'summary' => '苍澜阁是我的第一个微型社区，也是我的第一个完整的Web项目，它的诞生主要是因为自己想体验一把撸网站的整个流程，不精细不完美，旨在学习。由于本人一直无比热爱武侠剧，所以命名为苍澜阁。各位少侠们有任何建议，欢迎邮件我~~<br>在这里特别感谢我的两位好友：张敏童鞋和车芸艺童鞋！我之前一直都是在从事于Android开发，在Web及PHP面，我是个初学者，很感谢他们对我毫不吝惜的帮助！<br>最后，希望各位少侠使用愉快！',
            'author' => [
                'name' => '王婷',
                'english_name' => 'Vita',
                'sex' => '女',
                'age' => '90后',
                'email' => 'vitaain@163.com',
                'github' => 'https://github.com/VitaAin',
                'country' => '中国',
                'city' => '上海',
                'tags' => [
                    '90后伪清新',
                    '国产电视剧支持者',
                    '港剧脑残铁杆粉'
                ],
                'hobbies' => [
                    'Android',
                    '摄影'
                ],
                'education' => [[
                    'start' => '2012/09',
                    'end' => '2016/07',
                    'school' => '安徽师范大学',
                    'subject' => '电子信息工程',
                    'degree' => '学士'
                ]],
                'skills' => [
                    'it' => [
                        '语言: JAVA, Kotlin, C++, C, HTML, CSS, JS, PHP, VB',
                        '平台: Android, Web',
                        '框架：RxJava, RxAndroid, Laravel, Vue, Ionic, Angular'
                    ],
                    'other' => [
                        '英语六级'
                    ]
                ]
            ]];
        return $this->responseOk('OK', $data);
    }
}

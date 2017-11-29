<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['middleware' => 'cors', 'prefix' => 'v1'], function () {
    // Auth
    Route::post('user/register', 'AuthController@register'); // 注册
    Route::get('verify_email', 'AuthController@verifyToken'); // 验证注册码
    Route::post('user/login', 'AuthController@login'); // 登录
    Route::get('user/logout', 'AuthController@logout'); // 登出

    // Articles
    Route::resource('articles', 'ArticlesController'); // 文章
    Route::get('hot_articles', 'ArticlesController@hotArticles'); // 热门文章
    // Comments
    Route::get('articles/{article}/comments', 'CommentsController@index'); // 文章评论
    // Categories
    Route::get('categories', 'CategoriesController@index'); // 分类
    // Tags
    Route::resource('tags', 'TagsController'); // 标签
    Route::get('hot_tags', 'TagsController@hotTags'); // 热门标签
    // Likes
    Route::get('articles/{article}/likes', 'ArticlesController@likes'); //获取文章的所有点赞

    // User
    Route::resource('users', 'UserController');
    Route::post('edit_password', 'UserController@editPassword'); //修改密码
    Route::post('avatar/upload', 'UserController@avatarUpload'); //上传头像
    Route::post('edit_user_info', 'UserController@editUserInfo'); //修改个人信息
    Route::get('users/{user}/articles', 'UserController@userArticles'); //用户发表的文章
    Route::get('users/{user}/replies', 'UserController@userReplies'); //用户的回复
    Route::get('users/{user}/like_articles', 'UserController@likeArticles'); //用户的点赞文章
    Route::get('users/{user}/follow_users', 'UserController@followUsers'); //用户的关注
    Route::get('article/is_like','LikesController@isLike'); //用户是否点赞了一个话题
    Route::get('article/like','LikesController@likeThisArticle'); //用户点赞一个话题
    Route::get('user/is_follow','FollowsController@isFollow'); //用户是否关注一个用户
    Route::get('user/follow','FollowsController@followThisUser'); //用户关注一个用户
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

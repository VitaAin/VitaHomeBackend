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

    // User
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

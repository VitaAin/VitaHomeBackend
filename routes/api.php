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

//$api=app('Dingo\Api\Routing\Router');
//$api->version('v1',['namespace'=>'App\Http\Controllers'],function ($api){
//    $api->post('token', 'UserController@token');    //获取token
//    $api->post('refresh-token', 'UserController@refershToken'); //刷新token
//
//    $api->group(['middleware' => ['auth:api']], function($api) {
//        $api->post('logout', 'UserController@logout');    //登出
//        $api->get('me', 'UserController@me');    //关于我
//    });
//});


Route::group(['middleware' => 'cols', 'prefix' => 'v1'], function () {
    // Articles
    Route::resource('articles', 'ArticlesController');
    Route::get('hot_articles', 'ArticlesController@hotArticles');

    Route::get('articles/{article}/comments', 'CommentsController@index');

    Route::get('categories', 'CategoriesController@index');

    // Tags
    Route::resource('tags', 'TagsController');
    Route::get('hot_tags', 'TagsController@hotTags');
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

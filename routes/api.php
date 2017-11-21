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


Route::group(['middleware' => 'cors'/*, 'prefix' => 'v1'*/], function () {
    // Auth
    Route::post('user/login', 'AuthController@login');
    Route::post('user/register', 'AuthController@register');
    Route::get('user/logout', 'AuthController@logout');

    // Articles
    Route::resource('articles', 'ArticlesController');
    Route::get('hot_articles', 'ArticlesController@hotArticles');

    Route::get('articles/{article}/comments', 'CommentsController@index');

    Route::get('categories', 'CategoriesController@index');

    // Tags
    Route::resource('tags', 'TagsController');
    Route::get('hot_tags', 'TagsController@hotTags');

    // User
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

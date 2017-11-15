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


Route::group(['middleware'=>'cols','prefix' => 'v1'], function () {
    // Articles
    Route::resource('articles', 'ArticlesController');
    // Route::get('', );
    
    
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use Cache;
use DB;

class CategoriesController extends Controller
{
    public function index()
    {
//        $categories = Cache::get('Categories_cache');
//        if (empty($categories)) {
            $categories = DB::table('categories')
//                ->select('id', 'name', 'description')
                ->get();
//            Cache::put('Categories_cache', $categories, 10);
//        }
        return $this->responseOk('OK', $categories);
    }

    /**
     * action: POST, URI: /categories
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = [
            'name' => $request->get('name'),
            'articles_count' => 0
        ];
        $category = Category::create($data);
        $category->description = $request->get('description');
        $category->save();
        return $this->responseOk('OK', $category);
    }
}

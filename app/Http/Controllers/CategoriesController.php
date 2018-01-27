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
        $categories = DB::table('categories')
            ->get();
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
        $desc = $request->get('description');
        if (!empty($desc)) {
            $category->description = $desc;
        }
        $category->save();
        return $this->responseOk('OK', $category);
    }
}

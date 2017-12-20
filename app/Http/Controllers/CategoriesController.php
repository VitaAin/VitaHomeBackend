<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;

class CategoriesController extends Controller
{
    public function index()
    {
        $categories = Category::pluck('name', 'id')
            ->toArray();
        $data = [];
        foreach ($categories as $key => $category) {
            $data[] = ['id' => $key, 'name' => $category];
        }
        return $this->responseOk('OK', $data);
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
        return $this->responseOk('OK', $category);
    }
}

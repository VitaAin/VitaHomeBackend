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
}

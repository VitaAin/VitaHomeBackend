<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\ArticlesRepository;

class ArticlesController extends Controller
{
    /**
     * @var ArticlesRepository
     */
    protected $articlesRepository;

    public function __construct(ArticlesRepository $articlesRepository)
    {
        $this->articlesRepository = $articlesRepository;
    }

    public function index(Request $request)
    {
        $page = 1;
        if ($request->input('page')) {
            $page = $request->input('page');
        }

        $articles = $this->articlesRepository->getArticles($page, $request);

        if (!empty($articles)) {
            return $this->responseSuccess('OK', $articles);
        }

        return $this->responseError('查询失败');
    }
}

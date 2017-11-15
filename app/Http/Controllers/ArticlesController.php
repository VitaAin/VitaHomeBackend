<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\ArticlesRepository;

class ArticlesController extends Controller
{
    /**
    * @var ArticlesRepository
    */
    protected $acticlesRepository;

    public function __construct(ArticlesRepository $acticlesRepository)
    {
        $this->acticlesRepository = $acticlesRepository;
    }

    public function index(Request $request)
    {
        $page = 1;
        if ($request->input('page')) {
            $page = $request->input('page');
        }

        $articles = $this->acticlesRepository->getArticles($page, $request);
        
        if (! empty($articles)) {
            return $this->responseSuccess('OK', $articles);
        }

        return $this->responseError('查询失败');
    }
}

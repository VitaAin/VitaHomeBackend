<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\ArticlesRepository;
use App\Article;
use App\Tag;
use App\Category;
use Cache;

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

        return $this->responseError('Failed');
    }

    public function hotArticles()
    {
        $hotArticles = Cache::get('hotArticles_cache');
        if (empty($hotArticles)) {
            $hotArticles = Article::where([])
                ->orderBy('comments_count', 'desc')
                ->latest('updated_at')
                ->take(10)
                ->get();
            Cache::put('hotArticles_cache', $hotArticles, 10);
        }
        return $this->responseOk('OK', $this->hotArticles());
    }
}

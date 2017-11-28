<?php

namespace App\Http\Controllers;

use App\Http\Request\StoreArticleRequest;
use App\Transformer\ArticleLikesTransformer;
use Illuminate\Http\Request;
use App\Repositories\ArticlesRepository;
use App\Article;
use App\Tag;
use App\Category;
use Cache;
use Auth;

class ArticlesController extends Controller
{
    /**
     * @var ArticlesRepository
     */
    protected $articlesRepository;

    /**
     * @var ArticleLikesTransformer
     */
    protected $articleLikesTransformer;

    public function __construct(ArticlesRepository $articlesRepository, ArticleLikesTransformer $articleLikesTransformer)
    {
        $this->articlesRepository = $articlesRepository;
        $this->articleLikesTransformer = $articleLikesTransformer;
    }

    /**
     * action: GET, URI: /articles
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $page = 1;

        if ($request->input('page')) {
            $page = $request->input('page');
        }

        $articles = $this->articlesRepository->getArticles($page, $request);

        if (empty($articles)) {
            return $this->responseError('Failed');
        }

        return $this->responseOk('OK', $articles);
    }

    /**
     * Show the form for creating a new resource.
     * action: GET, URI: /articles/create
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * action: POST, URI: /articles
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $tags = $this->articlesRepository->createTags($request->get('tags'));
        $data = [
            'title' => $request->get('title'),
//            'article_url'=>$request->get('article_url'),
            'body' => $request->get('body'),
//            'user_id' => Auth::id(),
            'user_id' => $request->get('user_id'),
            'is_public' => $request->get('is_public'),
            'category_id' => $request->get('category_id')
        ];
        $article = $this->articlesRepository->create($data);
//        $article->increment('category_id');
        if ($category = Category::find($request->get('category'))) {
            $category->increment('articles_count');
        }
        Auth::user()->increment('articles_count');
        $article->tags()->attach($tags);
        Cache::tags('articles')->flush();

        return $this->responseOk('OK', $article);
    }

    /**
     * Display the specified resource.
     * action: GET, URI: /articles/{id}
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $article = $this->articlesRepository->getArticle($id);

        if (empty($article)) {
            return $this->responseError('Failed');
        }

        return $this->responseOk('OK', $article);
    }

    /**
     * Show the form for editing the specified resource.
     * action: GET, URI: /articles/{id}/edit
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * action: PUT/PATCH, URI: /articles/{id}
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = [
            'title' => $request->get('title'),
//            'article_url' => $request->get('article_url'),
            'body' => $request->get('body'),
            'is_public' => $request->get('is_public'),
            'category_id' => $request->get('category_id')
        ];
        $article = $this->articlesRepository->findArticleById($id);
        $article->update($data);

        $addTags = $this->articlesRepository->editTags($request->get('tags'), $id);
        if ($addTags) {
            foreach ($addTags as $addTag) {
                if (is_numeric($addTag)) {
                    $article->tags()->attach($addTag);
                    Tag::where('id', $addTag)->increment('count', 1);
                } else {
                    $article->tags()->create([
                        'name' => $addTag,
                        'article_count' => 1
                    ]);
                }
            }
        }
        Cache::tags('articles')->flush();
        return $this->responseOk('OK', $article);
    }

    /**
     * Remove the specified resource from storage.
     * action: DELETE, URI: /articles/{id}
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
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
        return $this->responseOk('OK', $hotArticles);
    }

    public function likes($id)
    {
        $article = Article::find($id);

        if (empty($article)) {
            return $this->responseError('No like for this article');
        } else {
            return $this->responseOk('OK',
                $this->articleLikesTransformer->transformCollection($article->likes->toArray()));
        }
    }
}

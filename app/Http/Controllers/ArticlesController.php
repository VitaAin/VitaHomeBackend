<?php

namespace App\Http\Controllers;

use App\Transformer\ArticleLikesTransformer;
use DB;
use Illuminate\Http\Request;
use App\Repositories\ArticlesRepository;
use App\Article;
use App\Tag;
use Cache;
use Auth;
use Log;
use Illuminate\Support\Facades\Storage;

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

        $this->middleware('jwt.auth', [
            'only' => ['store', 'update', 'destroy']
        ]);
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
        $category = $this->articlesRepository->createCategory($request->get('category'));

        $data = [
            'title' => $request->get('title'),
            'cover_url'=>$request->get('cover_url'),
            'body' => $request->get('body'),
            'user_id' => Auth::id(),
            'is_public' => $request->get('is_public'),
//            'category_id' => $request->get('category_id')
            'category_id' => $category
        ];
        $article = $this->articlesRepository->create($data);
        $this->articlesRepository->createImages($request->get('images'));
        Auth::user()->increment('articles_count');
//        Auth::user()->increment('images_count', count($images));
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
            'cover_url' => $request->get('cover_url'),
            'body' => $request->get('body'),
            'is_public' => $request->get('is_public'),
            'category_id' => $request->get('category_id')
        ];
        $article = $this->articlesRepository->findArticleById($id);
        $article->update($data);

        /*$addTags = */
        $this->articlesRepository->editTags($article /*$id*/, $request->get('tags'));
//        if ($addTags) {
//            foreach ($addTags as $addTag) {
//                if (is_numeric($addTag)) {
//                    $article->tags()->attach($addTag);
//                    Tag::where('id', $addTag)->increment('count', 1);
//                } else {
//                    $article->tags()->create([
//                        'name' => $addTag,
//                        'article_count' => 1
//                    ]);
//                }
//            }
//        }
        Cache::tags('articles')->flush();
        $this->articlesRepository->createImages($request->get('images'));
//        Auth::user()->increment('images_count', count($images));
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
            return $this->responseError('Cannot find this article');
        } else {
            $data = [
                'likes_count' => count($article->likes->toArray()),
                'likes' => $this->articleLikesTransformer
                    ->transformCollection($article->likes->toArray())
            ];
            return $this->responseOk('OK', $data);
        }
    }

    public function search()
    {
        Log::info("search content: " . request('content'));
//        $articles = Article::search(request('content'), null, true)->with('user')->paginate(20); // failed
//        $articles = Article::search(request('content'))->with('user')->paginate(20); // failed
//        $articles = Article::search(request('content'))->with('user')->get(); // ok
        $articles = Article::search(request('content'), null, true)->with('user')->get(); // failed
        return $this->responseOk('OK', $articles);
    }

}

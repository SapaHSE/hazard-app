<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsResource;
use App\Models\News;

class NewsController extends Controller
{
    // GET /api/news  (?category=K3+%2F+HSE)
    public function index()
    {
        $query = News::published()->latest('published_at');

        if (request()->filled('category')) {
            $query->where('category', request('category'));
        }

        return response()->json([
            'status' => 'success',
            'data'   => NewsResource::collection($query->get()),
        ]);
    }

    // GET /api/news/{id}
    public function show($id)
    {
        $news = News::published()->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => new NewsResource($news),
        ]);
    }
}
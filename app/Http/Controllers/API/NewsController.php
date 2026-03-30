<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\News;

class NewsController extends Controller
{
    // 🔹 GET ALL NEWS
    public function index()
    {
        $news = News::latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $news
        ]);
    }

    // 🔹 GET DETAIL NEWS
    public function show($id)
    {
        $news = News::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $news
        ]);
    }
}
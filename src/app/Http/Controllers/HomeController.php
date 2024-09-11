<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;

class HomeController extends Controller
{
    /**
     * Отображает главную страницу с Vue.js компонентом.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('home'); // Это загружает 'resources/views/home.blade.php'
    }

    /**
     * Возвращает данные комментариев для использования в Vue.js.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function comments()
    {
        $comments = Comment::all();
        return response()->json($comments);
    }
}



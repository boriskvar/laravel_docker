<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Получение списка всех комментариев.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $comments = Comment::whereNull('parent_id')->with('replies')->get();
        return response()->json($comments);
    }

    /**
     * Сохранение нового комментария.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:comments,id',
            'user_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'text' => 'required|string',
            'home_page' => 'nullable|url',
            'captcha' => 'required|string',
            'rating' => 'nullable|integer',
            'quote' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'file_path' => 'nullable|file|mimes:jpg,jpeg,png,gif,txt',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $filePath = null;
        if ($request->hasFile('file_path')) {
            $filePath = $request->file('file_path')->store('files', 'public');
        }

        $comment = new Comment();
        $comment->parent_id = $validated['parent_id'] ?? null;
        $comment->user_name = $validated['user_name'];
        $comment->email = $validated['email'];
        $comment->text = $validated['text'];
        $comment->home_page = $validated['home_page'] ?? null;
        $comment->captcha = $validated['captcha'];
        $comment->rating = $validated['rating'] ?? null;
        $comment->quote = $validated['quote'] ?? null;
        $comment->avatar = $avatarPath ? str_replace('public/', '', $avatarPath) : null; // Убираем 'public/' из пути
        $comment->file_path = $filePath ? str_replace('public/', '', $filePath) : null; // Убираем 'public/' из пути
        $comment->save();

        // Формируем URL для аватара и файла
        $comment->avatarUrl = $comment->avatar ? "/storage/{$comment->avatar}" : null;
        $comment->fileUrl = $comment->file_path ? "/storage/{$comment->file_path}" : null;


        return response()->json($comment, 201);
    }

}

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

        // Сохраняем аватар напрямую в public/avatars
        // Метод public_path() используется для указания директории в папке public, куда нужно сохранить файл
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->move(public_path('avatars'), $request->file('avatar')->getClientOriginalName());
        }

        // Сохраняем файл напрямую в public/files
        // Мы используем метод move() для сохранения файлов непосредственно в папку public вместо метода store(), который сохраняет файлы в папку storage.
        $filePath = null;
        if ($request->hasFile('file_path')) {
            $filePath = $request->file('file_path')->move(public_path('files'), $request->file('file_path')->getClientOriginalName());
        }

        $comment = new Comment();
        $comment->parent_id = $validated['parent_id'] ?? null;
        $comment->user_name = $validated['user_name'];
        $comment->email = $validated['email'];
        $comment->text = $validated['text'];
        $comment->home_page = $validated['home_page'] ?? null;
        $comment->captcha = $validated['captcha'];
        //$comment->rating = $validated['rating'] ?? null;
        $comment->rating = $validated['rating'] ?? 0;
        $comment->quote = $validated['quote'] ?? null;

        $comment->avatar = $avatarPath ? "avatars/{$request->file('avatar')->getClientOriginalName()}" : null;
        $comment->file_path = $filePath ? "files/{$request->file('file_path')->getClientOriginalName()}" : null;

        $comment->save();

        // Формируем URL для аватара и файла
        $comment->avatarUrl = $comment->avatar ? "avatars/{$request->file('avatar')->getClientOriginalName()}" : null;
        $comment->fileUrl = $comment->file_path ? "files/{$request->file('file_path')->getClientOriginalName()}" : null;


        return response()->json($comment, 201);
    }
    public function replies(Comment $comment)
    {
        $replies = $comment->replies;
        return response()->json($replies);
    }

    public function createReply(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'user_name' => 'required|string|max:255',
            'text' => 'required|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        // Сохраняем аватар напрямую в public/avatars
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->move(public_path('avatars'), $request->file('avatar')->getClientOriginalName());
        } // Закрывающая скобка добавлена здесь

        $reply = $comment->replies()->create([
            'user_name' => $validated['user_name'],
            'avatar' => $avatarPath ? "avatars/{$request->file('avatar')->getClientOriginalName()}" : null,
            'text' => $validated['text'],
            'parent_id' => $comment->id,
            // Другие необходимые поля, такие как user_id, можно добавить по мере необходимости
        ]);

        // Формируем URL для аватара
        $reply->avatarUrl = $reply->avatar ? "avatars/{$request->file('avatar')->getClientOriginalName()}" : null;

        // Загрузите вложенные ответы
        $reply->load('replies');

        return response()->json($reply, 201); // Возвращаем только что созданный ответ
    }
}

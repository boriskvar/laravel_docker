<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{

    /**
     * Получение списка всех комментариев.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $comments = Comment::with('replies')->get();
        return response()->json($comments);
    }

    private function isValidHtml($text)
    {
        $allowedTags = '<a><code><i><strong>';
        $cleanText = strip_tags($text, $allowedTags);
        Log::info('Cleaned text:', ['cleanText' => $cleanText]);

        return $cleanText === $text;
        //return true; // или false, в зависимости от проверки.
    }

    /**
     * Создание нового комментария.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        // Логирование входящих данных
        Log::info('Received request data:', $request->all());

        // Валидация входящих данных
        $validated = $request->validate([
            'user_name' => 'required|string|max:255',
            'avatar' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'home_page' => 'nullable|url',
            'captcha' => 'nullable|string',
            'text' => 'required|string',
            'rating' => 'nullable|integer',
            'quote' => 'nullable|string',
            'file_path' => 'nullable|file|mimes:jpg,jpeg,png,gif,txt|max:2048', // Допустимые форматы и максимальный размер файла
        ]);

        // Проверка HTML-тегов в тексте комментария
        if (!$this->isValidHtml($validated['text'])) {
            return response()->json(['error' => 'Некорректные HTML-теги в тексте комментария.'], 422);
        }

        // Создание записи комментария
        $comment = new Comment();
        $comment->user_name = $validated['user_name'];
        $comment->email = $validated['email'];
        $comment->text = $validated['text'];
        $comment->avatar = $validated['avatar'] ?? null;
        $comment->home_page = $validated['home_page'] ?? null;
        $comment->captcha = $validated['captcha'] ?? null;
        $comment->rating = $validated['rating'] ?? null;
        $comment->quote = $validated['quote'] ?? null;


        // Обработка файла
        if ($request->hasFile('file_path')) {
            $file = $request->file('file_path');
            // Сохранение файла в хранилище
            $path = $file->store('files', 'public'); // Путь для комментариев
            // Сохранение пути к файлу в базе данных
            $comment->file_path = $path;
        }

        // Сохранение комментария в базе данных
        $comment->save();

        // Возвращение ответа с созданным комментарием
        return response()->json($comment, 201);
    }


    /**
     * Показ конкретного комментария.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    /*public function show($id)
    {
        $comment = Comment::with('replies')->findOrFail($id);
        return response()->json($comment);
    }*/

    /**
     * Обновление существующего комментария.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    /*public function update(Request $request, $id)
    {
        $validated = $request->validate([

            'user_name' => 'required|string|max:255',
            'avatar' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'home_page' => 'nullable|url',
            'captcha' => 'nullable|string',
            'text' => 'required|string',
            'rating' => 'nullable|integer',
            'quote' => 'nullable|string',
        ]);

// Проверка HTML-тегов в комментарии
        if (!$this->isValidHtml($validated['text'])) {
            return response()->json(['error' => 'Некорректные HTML-теги в тексте комментария.'], 422);
        }

        $comment = Comment::findOrFail($id);
        $comment->update($validated);

        return response()->json($comment);
    }*/

    /**
     * Удаление существующего комментария.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    /*public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();
        return response()->json(null, 204);
    }*/

    /**
     * Проверка текста на наличие корректных разрешенных HTML-тегов.
     *
     * @param string $text
     * @return bool
     */
    /*private function isValidHtml($text)
    {
        // Разрешенные HTML-теги
        $allowedTags = '/<(\/)?(a|code|i|strong)(\s+(href="[^"]*"|title="[^"]*"))*\s*>/';

        // Проверка, что в тексте используются только разрешенные теги и они корректно закрыты
        return preg_match_all($allowedTags, $text) && strip_tags($text, '<a><code><i><strong>') === $text;
    }*/



}

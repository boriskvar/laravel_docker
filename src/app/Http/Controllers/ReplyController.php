<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reply;
use App\Models\Comment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReplyController extends Controller
{
    // Показ всех ответов для конкретного комментария
    public function index($commentId)
    {
        \Log::info("Attempting to get replies for comment with ID: {$commentId}");

        $replies = Reply::where('comment_id', $commentId)->whereNull('parent_id')->get();

        \Log::info("Replies found: ", $replies->toArray());

        return response()->json($replies);
    }
    private function isValidHtml($text)
    {
        $allowedTags = '<a><code><i><strong>';
        $cleanText = strip_tags($text, $allowedTags);
        \Log::info('Cleaned text:', ['cleanText' => $cleanText]);

        return $cleanText === $text;
    }

    // Создание нового ответа для комментария
    public function create(Request $request, $commentId)
    {
        // Логирование входящих данных
        Log::info('Received request data:', $request->all());
        // для проверки наличия файла в запросе
        Log::info('Files in request:', $request->files());

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
        'file_path' => 'nullable|file|mimes:jpg,jpeg,png,gif,txt|max:2048', // Имя поля должно совпадать
    ]);

 // Проверка  HTML-тегов в тексте комментария
 if (!$this->isValidHtml($validated['text'])) {
    return response()->json(['error' => 'Некорректные HTML-теги в тексте комментария.'], 422);
}

            // Создание записи ответа
    $reply = new Reply();
    $reply->user_name = $validated['user_name'];
    $reply->email = $validated['email'];
    $reply->text = $validated['text'];
    $reply->avatar = $validated['avatar'] ?? null;
    $reply->home_page = $validated['home_page'] ?? null;
    $reply->captcha = $validated['captcha'] ?? null;
    $reply->rating = $validated['rating'] ?? null;
    $reply->quote = $validated['quote'] ?? null;
    $reply->comment_id = $commentId; // Используем параметр из URL
    $reply->parent_id = $request->input('parent_id');

    // Обработка файла
    if ($request->hasFile('file_path')) {
        Log::info('Файл присутствует в запросе');
        $file = $request->file('file_path');
        Log::info('File detected:', ['file_name' => $file->getClientOriginalName()]);
        Log::info('File uploaded:', ['path' => $file->getRealPath(), 'name' => $file->getClientOriginalName()]);
        // Сохранение файла в хранилище
        $path = $file->store('files', 'public'); // Используем папку files как Путь для ответов
        Log::info('File path:', ['path' => $path]);
        Log::info('File URL:', ['url' => $reply->file_url]);

        // Сохранение пути к файлу в базе данных
        $reply->file_path = $path;
    } else {
        Log::info('No file uploaded');
    }

    // Сохранение ответа в базе данных
    $reply->save();

    // Возвращение ответа с созданным ответом
    return response()->json($reply, 201);
    //return response()->json($reply->fresh(), 201);
    }

    // Показ конкретного ответа
    /*public function show($replyId)
    {
        $reply = Reply::find($replyId);

        if (!$reply) {
            \Log::error('Reply not found with ID:', ['id' => $replyId]);
            return response()->json(['error' => 'Reply not found'], 404);
        }

        \Log::info('Reply found:', $reply->toArray());
        return response()->json($reply);
    }*/

    // Обновление ответа
    /*public function update(Request $request, $replyId)
    {
        $reply = Reply::findOrFail($replyId);

        $validatedData = $request->validate([
            'user_name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'text' => 'nullable|string',
        ]);

        \Log::info('Data to update reply:', $validatedData);
        $reply->update($validatedData);

        \Log::info('Updated reply:', $reply->toArray());
        return response()->json($reply);
    }*/

    // Удаление ответа
    /*public function destroy($commentId)
    {
        $comment = Comment::findOrFail($commentId);

        \Log::info('Comment found:', $comment->toArray());
        $comment->delete();

        \Log::info('Comment deleted with ID:', ['id' => $commentId]);
        return response()->json(['message' => 'Comment deleted']);
    }*/

    // Показ всех ответов для конкретного ответа
    public function indexReply($commentId)
    {
        \Log::info("Attempting to get replies for reply with ID: {$commentId}");

        $replies = Reply::where('comment_id', $commentId)->get();

        \Log::info("Replies found: ", $replies->toArray());
        return response()->json($replies);
    }

    // Создание нового ответа для ответа
    public function createReply(Request $request, $commentId)
    {
        $validatedData = $request->validate([
            'user_name' => 'required|string|max:255',
            'email' => 'required|email',
            'text' => 'required|string',
        ]);

        $parentComment = Comment::find($commentId);
        if (!$parentComment) {
            \Log::error("Parent comment with ID {$commentId} not found.");
            return response()->json(['error' => 'Parent comment not found'], 404);
        }

        $reply = new Reply($validatedData);
        $reply->comment_id = $commentId;
        $reply->parent_id = null; // Поскольку это ответ на комментарий, parent_id не используется
        $reply->save();

        \Log::info('Reply created for comment:', $reply->toArray());

        return response()->json($reply, 201);
    }


    // Показ конкретного ответа на ответ
    /*public function showReply($replyId)
    {
        $reply = Reply::find($replyId);

        if (!$reply) {
            \Log::error("Reply with ID {$replyId} not found.");
            return response()->json(['error' => 'Reply not found'], 404);
        }
        return response()->json($reply);
    }*/

    // Обновление ответа на ответ
    /*public function updateReply(Request $request, $replyId)
    {
        $reply = Reply::find($replyId);

        if (!$reply) {
            \Log::error("Reply with ID {$replyId} not found.");
            return response()->json(['error' => 'Reply not found'], 404);
        }

        $validatedData = $request->validate([
            'user_name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'text' => 'nullable|string',
        ]);

        $reply->update($validatedData);
        \Log::info('Updated reply:', $reply->toArray());
        return response()->json($reply);
    }*/

    // Удаление ответа на ответ
    /*public function destroyReply($replyId)
    {
        $reply = Reply::find($replyId);

        if (!$reply) {
        \Log::error("Reply with ID {$replyId} not found.");
        return response()->json(['error' => 'Reply not found'], 404);
    }

        $reply->delete();
        \Log::info('Reply deleted with ID:', ['id' => $replyId]);
        return response()->json(['message' => 'Reply deleted']);
    }*/

    // Показ всех ответов для конкретного ответа
    public function indexReplyReply($replyId)
    {
        \Log::info("Attempting to get replies for reply with ID: {$replyId}");

        // Получаем все дочерние ответы
        $replies = Reply::where('parent_id', $replyId)->get();

        \Log::info("Replies found: ", $replies->toArray());
        return response()->json($replies);
    }

    // Создание нового ответа для ответа
    public function createReplyReply(Request $request, $replyId)
    {
        // Валидируем входные данные
        $validatedData = $request->validate([
            'user_name' => 'required|string|max:255',
            'email' => 'required|email',
            'text' => 'required|string',
        ]);

        // Проверяем существование родительского ответа
        $parentReply = Reply::find($replyId);
        if (!$parentReply) {
            \Log::error("Parent reply with ID {$replyId} not found.");
            return response()->json(['error' => 'Parent reply not found'], 404);
        }

        // Создаем новый ответ
        $reply = new Reply($validatedData);
        $reply->comment_id = $parentReply->comment_id;
        $reply->parent_id = $replyId;
        $reply->save();

        \Log::info('New reply created for reply:', $reply->toArray());

        return response()->json($reply, 201);
    }

    // Показ конкретного ответа на ответ
    /*public function showReplyReply($parentId, $replyId)
    {
        $reply = Reply::where('id', $replyId)->where('parent_id', $parentId)->first();
        if (!$reply) {
            \Log::error("Reply with ID {$replyId} not found for parent reply ID {$parentId}.");
            return response()->json(['error' => 'Reply not found'], 404);
        }
        return response()->json($reply);
    }*/


    // Обновление ответа на ответ
    /*public function updateReplyReply(Request $request, $parentId, $replyId)
    {
        $reply = Reply::where('id', $replyId)->where('parent_id', $parentId)->first();
        if (!$reply) {
            \Log::error("Reply with ID {$replyId} not found for parent reply ID {$parentId}.");
            return response()->json(['error' => 'Reply not found'], 404);
        }

        $validatedData = $request->validate([
            'user_name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'text' => 'nullable|string',
        ]);

        $reply->update($validatedData);
        \Log::info('Updated reply:', $reply->toArray());
        return response()->json($reply);
    }*/


    // Удаление ответа на ответ
    /*public function destroyReplyReply($parentId, $replyId)
    {
        $reply = Reply::where('id', $replyId)->where('parent_id', $parentId)->first();
        if (!$reply) {
            \Log::error("Reply with ID {$replyId} not found for parent reply ID {$parentId}.");
            return response()->json(['error' => 'Reply not found'], 404);
        }

        $reply->delete();
        \Log::info('Reply deleted with ID:', ['id' => $replyId]);
        return response()->json(['message' => 'Reply deleted']);
    }*/

}

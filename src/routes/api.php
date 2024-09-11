<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ReplyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

// Основные маршруты для комментариев
Route::get('/comments', [CommentController::class, 'index']); // Показ всех комментариев
Route::post('/comments', [CommentController::class, 'create']); // Создание нового комментария
//Route::get('/comments/{commentId}', [CommentController::class, 'show']); // Показ конкретного комментария
//Route::put('/comments/{commentId}', [CommentController::class, 'update']); // Обновление комментария
//Route::delete('/comments/{commentId}', [CommentController::class, 'destroy']); // Удаление комментария

// Ресурсные маршруты для ответов на комментарии
Route::get('/comments/{commentId}/replies', [ReplyController::class, 'indexReply']); // Показ всех ответов для конкретного комментария
Route::post('/comments/{commentId}/replies', [ReplyController::class, 'createReply']); // Создание нового ответа для комментария
//Route::get('/replies/{replyId}', [ReplyController::class, 'showReply']); // Показ конкретного ответа
//Route::put('/replies/{replyId}', [ReplyController::class, 'updateReply']); // Обновление ответа
//Route::delete('/replies/{replyId}', [ReplyController::class, 'destroyReply']); // Удаление ответа

// Ресурсные маршруты для ответов на ответы
Route::get('/replies/{replyId}/replies', [ReplyController::class, 'indexReplyReply']); // Показ всех ответов для конкретного ответа
Route::post('/replies/{replyId}/replies', [ReplyController::class, 'createReplyReply']); // Создание нового ответа для ответа
//Route::get('/replies/{parentId}/replies/{replyId}', [ReplyController::class, 'showReplyReply']); // Показ конкретного ответа на ответ
//Route::put('/replies/{parentId}/replies/{replyId}', [ReplyController::class, 'updateReplyReply']); // Обновление ответа на ответ
//Route::delete('/replies/{parentId}/replies/{replyId}', [ReplyController::class, 'destroyReplyReply']); // Удаление ответа на ответ

Route::get('/captcha', [CaptchaController::class, 'generate']);

Route::post('/test', [TestController::class, 'store']);

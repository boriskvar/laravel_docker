<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable(); // Внешний ключ на родительский комментарий
            $table->string('user_name');
            $table->string('email')->nullable();
            $table->string('home_page')->nullable();
            $table->string('captcha')->nullable();
            $table->text('text');
            $table->timestamps();
            $table->string('avatar')->nullable();
            $table->string('file_path')->nullable(); // Поле для пути к файлу
            $table->text('quote')->nullable(); // Поле для цитаты
            $table->integer('rating')->default(0); // Поле для рейтинга

            // Индекс для быстрого поиска по parent_id
            $table->index('parent_id');

            // Добавляем внешний ключ
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('comments')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Удаление внешнего ключа
            $table->dropForeign(['parent_id']);

            // Удаление индекса
            $table->dropIndex(['parent_id']);

            // Удаление столбцов
            $table->dropColumn('rating');
            $table->dropColumn('quote');
        });

        Schema::dropIfExists('comments');
    }
};

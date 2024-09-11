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
        Schema::create('replies', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('comment_id');
            $table->unsignedBigInteger('parent_id')->nullable(); // Добавляем столбец для ссылок на другие ответы
            $table->string('user_name');
            $table->string('avatar')->nullable();
            $table->string('email');
            $table->string('home_page')->nullable();
            $table->text('text')->nullable();
            $table->string('file_path')->nullable();


            $table->timestamps();

            // Внешний ключ для связи с таблицей comments
            $table->foreign('comment_id')
                  ->references('id')
                  ->on('comments')
                  ->onDelete('cascade');

            // Внешний ключ для связи с таблицей replies (для вложенных ответов)
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('replies')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('replies', function (Blueprint $table) {
            // Удаление внешних ключей перед удалением столбцов или таблиц
            $table->dropForeign(['comment_id']);
            $table->dropForeign(['parent_id']);
          });

          Schema::dropIfExists('replies');
    }
};

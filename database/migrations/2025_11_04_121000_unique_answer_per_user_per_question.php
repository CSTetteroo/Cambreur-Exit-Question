<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('answers', function (Blueprint $table) {
            $table->unique(['question_id', 'user_id'], 'answers_question_user_unique');
        });
    }

    public function down(): void {
        Schema::table('answers', function (Blueprint $table) {
            $table->dropUnique('answers_question_user_unique');
        });
    }
};

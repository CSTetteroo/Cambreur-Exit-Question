<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('class_user', function (Blueprint $table) {
            // Restore foreign key from class_user.user_id to users.id
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::table('class_user', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
};

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
        Schema::table('class_user', function (Blueprint $table) {
            // Drop the foreign key constraint on user_id
            $table->dropForeign(['user_id']);
            // Drop the unique index on user_id
            $table->dropUnique('class_user_user_id_unique');
            // Add unique index on class_id + user_id
            $table->unique(['class_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('class_user', function (Blueprint $table) {
            $table->dropUnique(['class_id', 'user_id']);
            // Optionally, add back unique on user_id if needed
            // $table->unique('user_id');
        });
    }
};

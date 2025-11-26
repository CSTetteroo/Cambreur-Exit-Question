<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('class_grade_resets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('docent_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('reset_at');
            $table->unique(['class_id','docent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_grade_resets');
    }
};

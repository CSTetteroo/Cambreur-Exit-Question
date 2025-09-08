<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->enum('type', ['multiple_choice', 'open']);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete(); // docent
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('questions');
    }
};


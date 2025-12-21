<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('content_type', ['LESSON', 'VIDEO', 'DOCUMENT', 'LINK'])->default('LESSON');
            $table->string('external_link')->nullable(); // YouTube, Drive, or other external links
            $table->text('content')->nullable(); // For lesson text content (if any)
            $table->integer('order')->default(0); // For ordering content
            $table->timestamps();
            
            $table->index(['course_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_contents');
    }
};
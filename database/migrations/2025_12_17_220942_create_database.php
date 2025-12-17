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
        // Add custom fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('full_name')->after('name');
            $table->string('profile_picture')->nullable()->after('remember_token');
            $table->enum('role', ['STUDENT', 'INSTRUCTOR', 'ADMIN'])->default('STUDENT')->after('profile_picture');
        });

        // Create categories table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Create courses table
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_closed')->default(false);
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->timestamps();
        });

        // Create join_requests table
        Schema::create('join_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->timestamp('request_date')->useCurrent();
            $table->enum('status', ['PENDING', 'ACCEPTED', 'REJECTED'])->default('PENDING');
            $table->timestamps();
            
            $table->unique(['student_id', 'course_id']);
        });

        // Create enrollments table
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamps();
            
            $table->unique(['student_id', 'course_id']);
        });

        // Create notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            $table->string('message');
            $table->timestamp('date')->useCurrent();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // Create audit_logs table
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action');
            $table->text('details')->nullable();
            $table->timestamp('timestamp')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('join_requests');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('categories');
        
        // Remove custom fields from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['full_name', 'profile_picture', 'role']);
        });
    }
};
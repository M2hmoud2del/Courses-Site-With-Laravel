<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Course;
use App\Models\Notification;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Instructor
        $instructor = User::create([
            'name' => 'Jane Smith',
            'full_name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'role' => 'INSTRUCTOR',
        ]);

        // 2. Create Categories
        $catWeb = Category::create(['name' => 'Web Development', 'description' => 'Frontend and Backend technologies']);
        $catData = Category::create(['name' => 'Data Science', 'description' => 'AI, ML, and Statistics']);
        $catMobile = Category::create(['name' => 'Mobile App Development', 'description' => 'iOS and Android']);

        // 3. Create Courses
        Course::create([
            'title' => 'Web Development Fundamentals',
            'description' => 'Learn HTML, CSS, a JavaScript from scratch. Build responsive websites.',
            'price' => 49.99,
            'is_closed' => false,
            'instructor_id' => $instructor->id,
            'category_id' => $catWeb->id,
        ]);

        Course::create([
            'title' => 'Data Science with Python',
            'description' => 'Master data analysis, visualization, and machine learning using Python.',
            'price' => 129.99,
            'is_closed' => false,
            'instructor_id' => $instructor->id,
            'category_id' => $catData->id,
        ]);

        Course::create([
            'title' => 'Mobile App Development',
            'description' => 'Build native mobile applications for iOS and Android using React Native.',
            'price' => 139.99,
            'is_closed' => false,
            'instructor_id' => $instructor->id,
            'category_id' => $catMobile->id,
        ]);

        Course::create([
            'title' => 'React Advanced Patterns',
            'description' => 'Take your React skills to the next level with advanced patterns and performance optimization.',
            'price' => 89.99,
            'is_closed' => false,
            'instructor_id' => $instructor->id,
            'category_id' => $catWeb->id,
        ]);
        
        // 4. Create Notifications for the current user (assuming ID 1 or the user will register as ID 1)
         // We can't target a specific user easily if they haven't registered yet, so we'll skip this or targeted later.
         // But for testing, if a user exists:
         $firstUser = User::first();
         if($firstUser) {
             Notification::create([
                 'recipient_id' => $firstUser->id,
                 'message' => 'Welcome to the platform! Check out our new courses.',
             ]);
         }
    }
}

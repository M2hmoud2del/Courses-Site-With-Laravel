<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseContentCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_content_id',
        'course_id',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function content()
    {
        return $this->belongsTo(CourseContent::class, 'course_content_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}

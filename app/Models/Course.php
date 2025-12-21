<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'price',
        'is_closed',
        'instructor_id',
        'category_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_closed' => 'boolean',
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments', 'course_id', 'student_id')
            ->withPivot('progress', 'enrolled_at')
            ->withTimestamps();
    }

    public function joinRequests()
    {
        return $this->hasMany(JoinRequest::class);
    }

    // Relationship with Enrollments
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // Relationship with CourseContent
    public function contents()
    {
        return $this->hasMany(CourseContent::class);
    }

    // Get ordered contents
    public function orderedContents()
    {
        return $this->contents()->ordered()->get();
    }
}

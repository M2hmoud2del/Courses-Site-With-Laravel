<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'full_name',
        'email',
        'password',
        'profile_picture',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isStudent(): bool
    {
        return $this->role === 'STUDENT';
    }

    public function isInstructor(): bool
    {
        return $this->role === 'INSTRUCTOR';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollments', 'student_id', 'course_id')
            ->withPivot('progress', 'enrolled_at')
            ->withTimestamps();
    }

    public function joinRequests()
    {
        return $this->hasMany(JoinRequest::class, 'student_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'recipient_id');
    }
}

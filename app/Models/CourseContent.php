<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'content_type',
        'external_link',
        'content',
        'order'
    ];

    protected $casts = [
        'order' => 'integer'
    ];

    // Relationship with Course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Scope ordered by order field
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // Get content type label
    public function getTypeLabelAttribute()
    {
        return match($this->content_type) {
            'LESSON' => 'Lesson',
            'VIDEO' => 'Video',
            'DOCUMENT' => 'Document',
            'LINK' => 'Link',
            default => 'Unknown',
        };
    }

    // Get content type icon
    public function getTypeIconAttribute()
    {
        return match($this->content_type) {
            'LESSON' => 'fas fa-book-open',
            'VIDEO' => 'fas fa-video',
            'DOCUMENT' => 'fas fa-file',
            'LINK' => 'fas fa-link',
            default => 'fas fa-question',
        };
    }

    // Get content type color
    public function getTypeColorAttribute()
    {
        return match($this->content_type) {
            'LESSON' => 'blue',
            'VIDEO' => 'purple',
            'DOCUMENT' => 'green',
            'LINK' => 'yellow',
            default => 'gray',
        };
    }

    // Check if it's an external link
    public function isExternalLink()
    {
        return $this->content_type === 'LINK' || $this->content_type === 'VIDEO' || $this->content_type === 'DOCUMENT';
    }

    // Extract platform from external link
    public function getPlatformAttribute()
    {
        if (!$this->external_link) return null;

        $url = strtolower($this->external_link);
        
        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            return 'YouTube';
        } elseif (str_contains($url, 'drive.google.com')) {
            return 'Google Drive';
        } elseif (str_contains($url, 'dropbox.com')) {
            return 'Dropbox';
        } elseif (str_contains($url, 'onedrive.live.com')) {
            return 'OneDrive';
        } elseif (str_contains($url, 'vimeo.com')) {
            return 'Vimeo';
        } else {
            return 'External Link';
        }
    }

    // Generate embed URL for YouTube
    public function getEmbedUrlAttribute()
    {
        if ($this->platform !== 'YouTube') return $this->external_link;

        // Extract video ID from YouTube URL
        $url = $this->external_link;
        if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $url, $matches)) {
            $videoId = $matches[1];
        } elseif (preg_match('/youtu\.be\/([^\&\?\/]+)/', $url, $matches)) {
            $videoId = $matches[1];
        } else {
            return $this->external_link;
        }

        return "https://www.youtube.com/embed/{$videoId}";
    }

    // Relationship with completions
    public function completions()
    {
        return $this->hasMany(CourseContentCompletion::class);
    }

    // Check if content is completed by user
    public function isCompletedBy(User $user)
    {
        return $this->completions()->where('user_id', $user->id)->exists();
    }
}
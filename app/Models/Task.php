<?php

namespace App\Models;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Tag;
use App\Models\TaskUpdate;
use App\Models\TimeEntry;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['title', 'description', 'status', 'priority', 'due_date', 'type', 'created_by'];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignees()
    {
        // Using 'task_assigned_tos' as defined in migration, but standard convention might be considered
        return $this->belongsToMany(User::class, 'task_assigned_tos');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class)->latest();
    }

    public function updates()
    {
        return $this->hasMany(TaskUpdates::class)->latest();
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'subject')->latest();
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class)->latest();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'task_tag');
    }
}

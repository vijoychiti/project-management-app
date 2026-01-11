<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Attachment extends Model
{
    protected $fillable = ['task_id', 'user_id', 'file_path', 'original_name', 'mime_type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

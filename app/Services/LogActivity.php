<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;

class LogActivity
{
    public static function record($action, $description = null, $subject = null)
    {
        ActivityLog::create([
            'user_id' => Auth::check() ? Auth::id() : null,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'ip_address' => Request::ip(),
        ]);
    }
}

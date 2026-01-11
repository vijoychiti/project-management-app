<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = \App\Models\ActivityLog::with('user')->latest()->paginate(20);
        return view('admin.activity_logs.index', compact('logs'));
    }
}

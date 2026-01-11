<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'body' => 'required_without:attachment|string',
            'attachment' => 'nullable|file|max:10240', // 10MB max
        ]);

        if ($request->filled('body')) {
            $task->comments()->create([
                'user_id' => Auth::id(),
                'body' => $validated['body'],
            ]);
            
            \App\Services\LogActivity::record('comment_task', "Commented on task: {$task->title}", $task);
        }

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('attachments', 'public');

            $task->attachments()->create([
                'user_id' => Auth::id(),
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
            ]);

            \App\Services\LogActivity::record('upload_attachment', "Uploaded file to task: {$task->title}", $task);
        }

        return back()->with('success', 'Comment/Attachment added.');
    }
}

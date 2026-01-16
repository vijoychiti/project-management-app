<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Task $task, \App\Services\OneSignalService $oneSignalService)
    {
        $validated = $request->validate([
            'body' => 'required_without:attachment|string',
            'attachment' => 'nullable|file|max:10240|extensions:jpg,jpeg,png,pdf,doc,docx,txt,xlsx,xls,csv', // 10MB max
        ]);

        try {

            $recipients = [];
            $activity   = null;


            DB::transaction(function () use ($task, $request, $validated, &$recipients, &$activity) {
                $comment = null;
                if ($request->filled('body')) {
                    $comment  = $task->comments()->create([
                        'user_id' => Auth::id(),
                        'body' => $validated['body'],
                    ]);

                    \App\Services\LogActivity::record('comment_task', "Commented on task: {$task->title}", $task);

                    $activity = 'commented';
                }

                if ($request->hasFile('attachment')) {
                    $file = $request->file('attachment');

                    $path = $file->storeAs('attachments', $file->hashName(), [
                        'disk'          => config('filesystems.default'),
                        'visibility'    => 'public',
                    ]);

                    $task->attachments()->create([
                        'user_id' => Auth::id(),
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'attachable_type' => $comment ? get_class($comment) : null,
                        'attachable_id' => $comment ? $comment->id : null
                    ]);

                    \App\Services\LogActivity::record('upload_attachment', "Uploaded file to task: {$task->title}", $task);

                    // Notify participants
                    $recipients = $task->assignees->pluck('id')->push($task->created_by)
                        ->reject(fn($id) => $id == Auth::id())
                        ->unique()
                        ->values()
                        ->toArray();

                    $activity = $activity ? 'commented and uploaded a file' : 'uploaded a file';
                }
            });

            DB::afterCommit(function () use ($task, $recipients, $oneSignalService, $activity) {
                if (!empty($recipients)) {
                    $oneSignalService->sendNotification(
                        $recipients,
                        'New Activity on Task',
                        Auth::user()->name . $activity . $task->title,
                        route('tasks.show', $task->id)
                    );
                }
            });

            return back()->with('success', '' . $activity . ' successfully.');
        } catch (\Throwable $th) {
            Log::error('Task comment creation failed', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return back()->withInput()->with('error', 'Something went wrong.');
        }
    }
}

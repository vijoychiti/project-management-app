<?php

namespace App\Http\Controllers;

use App\Models\SubTask;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SubTaskController extends Controller
{
    public function store(Request $request, Task $task, \App\Services\OneSignalService $oneSignalService)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'type' => ['required', Rule::in(['bug', 'feature', 'improvement'])],
            'assigned_to' => 'nullable|exists:users,id',
            'attachments.*' => 'nullable|file|max:10240,accept:jpg,jpeg,png,pdf,doc,docx,txt,xlsx,xls,csv', // 10MB max per file
        ]);

        try {

            $subTask = null;
            DB::transaction(function () use ($task, $validated, $request, &$subTask) {
                $subTask = $task->subTasks()->create([
                    'title' => $validated['title'],
                    'priority' => $validated['priority'],
                    'type' => $validated['type'],
                    'assigned_to' => $validated['assigned_to'] ?? null,
                    'status' => 'todo',
                ]);

                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $path = $file->storeAs('attachments', $file->hashName(), [
                            'disk' => config('filesystems.default'),
                            'visibility' => 'public',
                        ]);

                        $task->attachments()->create([
                            'user_id' => Auth::id(),
                            'file_path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getClientMimeType(),
                            'attachable_type' => SubTask::class,
                            'attachable_id' => $subTask->id,
                        ]);
                    }
                }

                \App\Services\LogActivity::record('create_subtask', "Created subtask: {$subTask->title}", $subTask);
            });

            DB::afterCommit(function () use ($task, $oneSignalService, $subTask) {

                // Reload relationship to be safe
                $task->load('assignees');

                $userIds = empty($subTask->assigned_to) ? $task->assignees->pluck('id')->toArray() : [strval($subTask->assigned_to)];

                if ($userIds) {
                    $oneSignalService->sendNotification(
                        $userIds,
                        'New Sub-task Assigned',
                        'You have been assigned to sub-task from task: ' . $task->title,
                        route('tasks.show', $task->id)
                    );
                }
            });

            return back()->with('success', 'Sub-task created successfully.');
        } catch (\Throwable $th) {
            Log::error('Sub-task creation failed', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function update(Request $request, SubTask $subTask, \App\Services\OneSignalService $oneSignalService)
    {

        try {
            $userIds = [];
            DB::transaction(function () use ($subTask, $request, &$userIds) {
                if (!Auth::user()->isAdmin()) {
                    $validated = $request->validate([
                        'status' => ['required', Rule::in(['todo', 'in_progress', 'done'])],
                    ]);
                    // Only update status
                    $subTask->update(['status' => $validated['status']]);
                    $userIds = [strval($subTask->task->created_by)];
                } else {
                    $validated = $request->validate([
                        'title' => 'sometimes|required|string|max:255',
                        'priority' => ['sometimes', 'required', Rule::in(['low', 'medium', 'high', 'urgent'])],
                        'type' => ['sometimes', 'required', Rule::in(['bug', 'feature', 'improvement'])],
                        'status' => ['sometimes', 'required', Rule::in(['todo', 'in_progress', 'done'])],
                        'assigned_to' => 'nullable|exists:users,id',
                    ]);
                    $subTask->update($validated);
                    $userIds = empty($subTask->assigned_to) ? $subTask->task->assignees->pluck('id')->toArray() : [strval($subTask->assigned_to)];
                }

                $changes = implode(', ', array_keys($subTask->getChanges()));
                \App\Services\LogActivity::record('update_subtask', "Updated subtask ({$changes}): {$subTask->title}", $subTask);
            });

            DB::afterCommit(function () use ($userIds, $oneSignalService, $subTask) {
                if ($userIds) {
                    $oneSignalService->sendNotification(
                        $userIds,
                        'Sub-task Updated',
                        'Sub-task has been updated',
                        route('tasks.show', $subTask->task_id)
                    );
                }
            });

            return back()->with('success', 'Sub-task updated successfully.');
        } catch (\Throwable $th) {
            Log::error('Sub-task update failed', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function destroy(SubTask $subTask)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::transaction(function () use ($subTask) {
                $subTask->delete();
                \App\Services\LogActivity::record('delete_subtask', "Deleted subtask: {$subTask->title}", $subTask);
            });

            return back()->with('success', 'Sub-task deleted successfully.');
        } catch (\Throwable $th) {
            Log::error('Sub-task deletion failed', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }
}

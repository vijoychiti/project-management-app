<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Display a listing of the tasks.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $status = $request->input('status');

        $query = Task::with('assignees', 'creator', 'tags');

        // Status Filter
        if ($status && in_array($status, ['todo', 'in_progress', 'done'])) {
            $query->where('status', $status);
        } elseif ($status === 'all') {
            // No status filtering
        } else {
            $query->whereIn('status', ['todo', 'in_progress']); // Default view
        }

        // Keyword Search
        if ($request->filled('keyword')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->keyword . '%')
                  ->orWhere('description', 'like', '%' . $request->keyword . '%');
            });
        }

        // Assignee Filter
        if ($request->filled('assignee_id')) {
            $query->whereHas('assignees', function($q) use ($request) {
                $q->where('users.id', $request->assignee_id);
            });
        }

        // Tag Filter
        if ($request->filled('tag_id')) {
            $query->whereHas('tags', function($q) use ($request) {
                $q->where('tags.id', $request->tag_id);
            });
        }

        // Date Range Filter
        if ($request->filled('date_from')) {
            $query->whereDate('due_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('due_date', '<=', $request->date_to);
        }

        // Removed user-based filtering as per request (Global Visibility)
        
        $tasks = $query->latest()->get(); // Use pagination if list gets long, but get() for now.

        // Data for filters
        $users = User::all();
        $tags = \App\Models\Tag::all();

        return view('tasks.index', compact('tasks', 'users', 'tags'));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        $users = User::whereIn('role', ['project_manager', 'developer'])->get();
        $tags = \App\Models\Tag::all();
        return view('tasks.create', compact('users', 'tags'));
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request, \App\Services\OneSignalService $oneSignalService)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'type' => ['required', Rule::in(['bug', 'feature', 'improvement'])],
            'due_date' => ['nullable', 'date'],
            'assignees' => ['required', 'array'],
            'assignees.*' => ['exists:users,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'type' => $validated['type'],
            'due_date' => $validated['due_date'],
            'created_by' => Auth::id(),
            'status' => 'todo',
        ]);

        $task->assignees()->sync($validated['assignees']);

        if (!empty($validated['tags'])) {
            $task->tags()->sync($validated['tags']);
        }

        \App\Services\LogActivity::record('create_task', "Created task: {$task->title}", $task);

        // Send Push Notification to Assignees
        $oneSignalService->sendNotification(
            $task->assignees->pluck('id')->toArray(),
            'New Task Assigned',
            'You have been assigned to task: ' . $task->title,
            route('tasks.show', $task->id)
        );

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task)
    {
        $task->load(['assignees', 'updates.user', 'creator']);
        return view('tasks.show', compact('task'));
    }

    /**
     * Display a Kanban board of tasks.
     */
    public function kanban(Request $request)
    {
        $user = Auth::user();
        $query = Task::query()->with(['assignees', 'creator', 'tags']);

        if ($request->has('user_id')) {
            $query->whereHas('assignees', function ($q) use ($request) {
                $q->where('users.id', $request->user_id);
            });
        }

        // Removed user-based filtering (Global Visibility)
        
        $allTasks = $query->get();
        
        $todoTasks = $allTasks->where('status', 'todo');
        $inProgressTasks = $allTasks->where('status', 'in_progress');
        $doneTasks = $allTasks->where('status', 'done');

        return view('tasks.kanban', compact('todoTasks', 'inProgressTasks', 'doneTasks'));
    }

    public function attachTag(Request $request, Task $task)
    {
        $request->validate(['tag_id' => 'required|exists:tags,id']);
        $task->tags()->syncWithoutDetaching([$request->tag_id]);
        
        \App\Services\LogActivity::record('update_task', "Added tag to task: {$task->title}", $task);
        
        return back()->with('success', 'Tag added.');
    }

    public function detachTag(Task $task, \App\Models\Tag $tag)
    {
        $task->tags()->detach($tag->id);
        
        \App\Services\LogActivity::record('update_task', "Removed tag from task: {$task->title}", $task);
        
        return back()->with('success', 'Tag removed.');
    }

    /**
     * Update the task status.
     */
    public function updateStatus(Request $request, Task $task, \App\Services\OneSignalService $oneSignalService)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['todo', 'in_progress', 'done'])],
        ]);
        
        $oldStatus = $task->status;
        $task->update(['status' => $validated['status']]);

        // Logic to track status change in TaskUpdates table (existing)
        // ...
        
        \App\Services\LogActivity::record(
            'update_task_status', 
            "Updated status from $oldStatus to {$task->status}", 
            $task
        );

        // Notify participants about status change
        $recipients = $task->assignees->pluck('id')->push($task->created_by)
            ->reject(fn($id) => $id == Auth::id())
            ->unique()
            ->values()
            ->toArray();

        $oneSignalService->sendNotification(
            $recipients,
            'Task Status Updated',
            "Task '{$task->title}' moved to " . str_replace('_', ' ', $task->status),
            route('tasks.show', $task->id)
        );

        return back()->with('success', 'Task status updated.');
    }

    public function destroy(Task $task)
    {
        $user = Auth::user();

        if ($user->role !== 'admin' && $user->id !== $task->created_by) {
            abort(403, 'Unauthorized action.');
        }

        \App\Services\LogActivity::record('delete_task', "Deleted task: {$task->title}", $task);
        
        $task->delete();

        return back()->with('success', 'Task deleted successfully.');
    }
}

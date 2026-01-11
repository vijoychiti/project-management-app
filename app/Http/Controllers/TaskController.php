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
    public function index()
    {
        // Admin sees all, PMs and Devs see assigned or created by them
        // For simplicity initially, let's just show all or filter by user role.
        // Assuming simplistic roles for now as per request.
        
        $user = Auth::user();
        
        $status = request('status');

        $query = Task::with('assignees', 'creator');

        if ($status && in_array($status, ['todo', 'in_progress', 'done'])) {
            $query->where('status', $status);
        } elseif ($status === 'all') {
            // No status filtering
        } else {
            $query->whereIn('status', ['todo', 'in_progress']);
        }

        if ($user->role !== 'admin') {
            $query->where(function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('assignees', function($sq) use ($user) {
                      $sq->where('users.id', $user->id);
                  });
            });
        }
        
        $tasks = $query->latest()->get();

        return view('tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        $users = User::whereIn('role', ['project_manager', 'developer'])->get();
        return view('tasks.create', compact('users'));
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'type' => ['required', Rule::in(['bug', 'feature', 'improvement'])],
            'due_date' => ['nullable', 'date'],
            'assignees' => ['required', 'array'],
            'assignees.*' => ['exists:users,id'],
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

        \App\Services\LogActivity::record('create_task', "Created task: {$task->title}", $task);

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
        $query = Task::query()->with(['assignees', 'creator', 'tags']); // Eager load tags

        if ($request->has('user_id')) {
            $query->whereHas('assignees', function ($q) use ($request) {
                $q->where('users.id', $request->user_id);
            });
        }

        if ($user->role !== 'admin') {
            $query->where(function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('assignees', function($sq) use ($user) {
                      $sq->where('users.id', $user->id);
                  });
            });
        }
        
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
    public function updateStatus(Request $request, Task $task)
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

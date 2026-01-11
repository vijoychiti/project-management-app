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

    /**
     * Display a Kanban board of tasks.
     */
    public function kanban()
    {
        $user = Auth::user();

        $query = Task::with('assignees', 'creator');

        if ($user->role !== 'admin') {
            $query->where(function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('assignees', function($sq) use ($user) {
                      $sq->where('users.id', $user->id);
                  });
            });
        }
        
        $tasks = $query->latest()->get();
        
        // Group tasks by status for the view
        $todoTasks = $tasks->where('status', 'todo');
        $inProgressTasks = $tasks->where('status', 'in_progress');
        $doneTasks = $tasks->where('status', 'done');

        return view('tasks.kanban', compact('todoTasks', 'inProgressTasks', 'doneTasks'));
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

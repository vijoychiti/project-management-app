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
            'assignees' => ['required', 'array'],
            'assignees.*' => ['exists:users,id'],
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'created_by' => Auth::id() ?? 1, // Fallback to 1 if no auth for dev testing, but should be authed
            'status' => 'todo',
        ]);

        $task->assignees()->attach($validated['assignees']);

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

        $task->update(['status' => $validated['status']]);

        return back()->with('success', 'Task status updated.');
    }
}

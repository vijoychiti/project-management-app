@extends('layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Tasks</h1>
        <div class="flex space-x-2">
            <a href="{{ route('tasks.kanban') }}"
                class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">Kanban View</a>
            <a href="{{ route('tasks.create') }}"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">Create New Task</a>
        </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6 border border-gray-100">
        <form action="{{ route('tasks.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <!-- Preserve Status -->
            @if (request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Keyword</label>
                <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Search..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2 border">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Assignee</label>
                <select name="assignee_id"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2 border">
                    <option value="">All Users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ request('assignee_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tag</label>
                <select name="tag_id"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2 border">
                    <option value="">All Tags</option>
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->id }}" {{ request('tag_id') == $tag->id ? 'selected' : '' }}>
                            {{ $tag->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2 flex gap-2">
                <div class="flex-grow">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Due Date</label>
                    <div class="flex gap-2">
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="w-full rounded-md border-gray-300 p-2 border text-sm" placeholder="From">
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="w-full rounded-md border-gray-300 p-2 border text-sm" placeholder="To">
                    </div>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700 h-9 flex items-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                    @if (request()->anyFilled(['keyword', 'assignee_id', 'tag_id', 'date_from', 'date_to']))
                        <a href="{{ route('tasks.index', ['status' => request('status')]) }}"
                            class="ml-2 text-gray-500 hover:text-gray-700 text-sm flex items-center h-9">Clear</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            @php
                $currentStatus = request('status', 'default');
                $params = request()->except('status'); // Keep other filters
            @endphp

            <a href="{{ route('tasks.index', array_merge($params, ['status' => 'all'])) }}"
                class="@if ($currentStatus == 'all') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                All
            </a>

            <a href="{{ route('tasks.index', array_merge($params, ['status' => 'todo'])) }}"
                class="@if ($currentStatus == 'todo') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                To Do
            </a>

            <a href="{{ route('tasks.index', array_merge($params, ['status' => 'in_progress'])) }}"
                class="@if ($currentStatus == 'in_progress') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                In Progress
            </a>

            <a href="{{ route('tasks.index', array_merge($params, ['status' => 'done'])) }}"
                class="@if ($currentStatus == 'done') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Done
            </a>
        </nav>
    </div>

    @if ($tasks->count() > 0)
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($tasks as $task)
                <a href="{{ route('tasks.show', $task) }}"
                    class="block bg-white shadow rounded-lg hover:shadow-lg transition p-6 border-l-4 
                @if ($task->status == 'todo') border-gray-400
                @elseif($task->status == 'in_progress') border-blue-500
                @else border-green-500 @endif">
                    <div class="flex justify-between items-start mb-2">
                        <span
                            class="px-2 py-1 text-xs font-semibold rounded 
                        @if ($task->status == 'todo') bg-gray-100 text-gray-800
                        @elseif($task->status == 'in_progress') bg-blue-100 text-blue-800
                        @else bg-green-100 text-green-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </span>
                        <div class="flex flex-col items-end">
                            <span class="text-xs text-gray-500 mb-1">{{ $task->created_at->diffForHumans() }}</span>
                            @if ($task->due_date)
                                <span
                                    class="text-xs font-bold @if ($task->due_date < now() && $task->status != 'done') text-red-600 @else text-gray-600 @endif">
                                    Due: {{ $task->due_date->format('M d') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 mb-2">
                        <span
                            class="px-2 py-0.5 text-xs rounded border 
                            @if ($task->priority == 'urgent') border-red-500 text-red-700 bg-red-50
                            @elseif($task->priority == 'high') border-orange-500 text-orange-700 bg-orange-50
                            @elseif($task->priority == 'medium') border-yellow-500 text-yellow-700 bg-yellow-50
                            @else border-gray-300 text-gray-600 bg-gray-50 @endif">
                            {{ ucfirst($task->priority) }}
                        </span>
                        <span class="px-2 py-0.5 text-xs rounded border border-gray-200 bg-white text-gray-600">
                            {{ ucfirst($task->type) }}
                        </span>
                        @foreach ($task->tags as $tag)
                            <span class="px-2 py-0.5 text-xs rounded-full text-white"
                                style="background-color: {{ $tag->color }}">
                                {{ $tag->name }}
                            </span>
                        @endforeach
                    </div>

                    <h3 class="text-lg font-bold text-gray-900 mb-2 truncate">{{ $task->title }}</h3>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $task->description }}</p>

                    <div class="flex items-center justify-between mt-4">
                        <div class="flex -space-x-2 overflow-hidden">
                            @foreach ($task->assignees->take(3) as $assignee)
                                <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-gray-300 flex items-center justify-center text-xs font-bold text-gray-700"
                                    title="{{ $assignee->name }}">
                                    {{ substr($assignee->name, 0, 2) }}
                                </div>
                            @endforeach
                            @if ($task->assignees->count() > 3)
                                <div
                                    class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-500">
                                    +{{ $task->assignees->count() - 3 }}
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-500">By: {{ $task->creator->name ?? 'Unknown' }}</span>
                            @if (Auth::user()->role === 'admin' || Auth::id() === $task->created_by)
                                <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                                    onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="text-center py-20 bg-white rounded-lg shadow">
            <h3 class="mt-2 text-sm font-medium text-gray-900">No tasks found</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating a new task.</p>
            <div class="mt-6">
                <a href="{{ route('tasks.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    Create Task
                </a>
            </div>
        </div>
    @endif
@endsection

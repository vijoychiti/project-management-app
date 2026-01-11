@extends('layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Tasks</h1>
        <a href="{{ route('tasks.create') }}"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">Create New Task</a>
    </div>

    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            @php
                $currentStatus = request('status', 'default');
                // Maps query param to a human readable label. 'default' implies todo+in_progress
            @endphp
            
            <a href="{{ route('tasks.index', ['status' => 'all']) }}"
               class="@if($currentStatus == 'all') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                All
            </a>

            <a href="{{ route('tasks.index', ['status' => 'todo']) }}"
               class="@if($currentStatus == 'todo') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                To Do
            </a>

            <a href="{{ route('tasks.index', ['status' => 'in_progress']) }}"
               class="@if($currentStatus == 'in_progress') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                In Progress
            </a>

            <a href="{{ route('tasks.index', ['status' => 'done']) }}"
               class="@if($currentStatus == 'done') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
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
                        <span class="text-xs text-gray-500">{{ $task->created_at->diffForHumans() }}</span>
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
                        <span class="text-xs text-gray-500">By: {{ $task->creator->name ?? 'Unknown' }}</span>
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

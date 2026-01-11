@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Task Details Column -->
        <!-- Task Details Column -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h1 class="text-xl font-bold text-gray-900 truncate">
                        {{ $task->title }}
                    </h1>
                    <span
                        class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide
                        @if ($task->status === 'todo') bg-gray-200 text-gray-800 
                        @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800 
                        @else bg-green-100 text-green-800 @endif">
                        {{ str_replace('_', ' ', $task->status) }}
                    </span>
                </div>

                <div class="p-6">
                    <div class="prose max-w-none text-gray-700 mb-8">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Description</h3>
                        <p class="whitespace-pre-line">{{ $task->description }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-100 pt-6">
                        <div>
                            <span class="block text-sm font-medium text-gray-500 mb-1">Priority</span>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                @if ($task->priority === 'urgent') bg-red-100 text-red-800 
                                @elseif($task->priority === 'high') bg-orange-100 text-orange-800 
                                @elseif($task->priority === 'medium') bg-yellow-100 text-yellow-800 
                                @else bg-green-100 text-green-800 @endif">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-500 mb-1">Type</span>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                {{ ucfirst($task->type) }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-500 mb-1">Due Date</span>
                            <span class="text-sm text-gray-900 font-medium">
                                {{ $task->due_date ? $task->due_date->format('M d, Y') : 'No Due Date' }}
                                @if ($task->due_date && $task->due_date->isPast() && $task->status !== 'done')
                                    <span class="text-red-600 font-bold text-xs ml-1">(Overdue)</span>
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-500 mb-1">Created By</span>
                            <div class="flex items-center">
                                <span
                                    class="text-sm text-gray-900 font-medium">{{ $task->creator->name ?? 'Unknown' }}</span>
                                <span class="text-xs text-gray-500 ml-2">({{ $task->created_at->format('M d, Y') }})</span>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <span class="block text-sm font-medium text-gray-500 mb-2">Assigned To</span>
                            <div class="flex flex-wrap gap-2">
                                @forelse($task->assignees as $assignee)
                                    <div class="flex items-center bg-gray-50 rounded-full px-3 py-1 border border-gray-200">
                                        <div
                                            class="h-5 w-5 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs mr-2">
                                            {{ substr($assignee->name, 0, 2) }}
                                        </div>
                                        <span class="text-sm text-gray-700">{{ $assignee->name }}</span>
                                    </div>
                                @empty
                                    <span class="text-sm text-gray-500 italic">No assignees</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Updates Section -->
            <!-- Discussion & Activity -->
            <div class="space-y-6">
                <!-- Comments & Attachments -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Discussion & Attachments</h3>

                    <div class="space-y-6 mb-8">
                        @php
                            $combined = $task->comments->concat($task->attachments)->sortByDesc('created_at');
                        @endphp

                        @forelse($combined as $item)
                            <div class="flex space-x-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center font-bold text-gray-600">
                                        {{ substr($item->user->name ?? 'U', 0, 2) }}
                                    </div>
                                </div>
                                <div class="flex-grow">
                                    <div class="text-sm">
                                        <span
                                            class="font-medium text-gray-900">{{ $item->user->name ?? 'Unknown User' }}</span>
                                        <span class="text-gray-500">
                                            @if ($item instanceof App\Models\Comment)
                                                commented
                                            @else
                                                uploaded a file
                                            @endif
                                        </span>
                                        <span class="text-gray-400 mx-1">&middot;</span>
                                        <span class="text-gray-400">{{ $item->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="mt-1 text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-100">
                                        @if ($item instanceof App\Models\Comment)
                                            {{ $item->body }}
                                        @else
                                            <div class="flex items-center space-x-2">
                                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                </svg>
                                                <a href="{{ Storage::url($item->file_path) }}" target="_blank"
                                                    class="text-indigo-600 hover:text-indigo-800 underline">
                                                    {{ $item->original_name }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 italic text-center py-4">No comments or files yet.</p>
                        @endforelse
                    </div>

                    <!-- Add Comment/File Form -->
                    <form action="{{ route('tasks.comments.store', $task) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="body" class="sr-only">Comment</label>
                            <textarea name="body" id="body" rows="3"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-3 border"
                                placeholder="Add a comment..."></textarea>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <label for="attachment"
                                    class="cursor-pointer text-gray-500 hover:text-indigo-600 flex items-center space-x-1">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                    <span class="text-sm">Attach File</span>
                                </label>
                                <input type="file" name="attachment" id="attachment" class="hidden"
                                    onchange="document.getElementById('file-name').textContent = this.files[0].name">
                                <span id="file-name" class="ml-2 text-xs text-gray-500"></span>
                            </div>
                            <button type="submit"
                                class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700 transition text-sm font-medium">
                                Post
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Task Updates (Separated) -->
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <h4 class="text-sm font-bold text-gray-700 mb-4 uppercase tracking-wide">System Updates</h4>
                    <div class="space-y-4">
                        @forelse($task->updates as $update)
                            <div class="text-sm text-gray-600">
                                <span class="font-medium text-gray-800">{{ $update->user->name ?? 'System' }}</span>
                                {{ $update->update }}
                                <span class="text-gray-400 text-xs ml-1">{{ $update->created_at->diffForHumans() }}</span>
                            </div>
                        @empty
                            <p class="text-gray-400 italic text-xs">No system updates.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="space-y-6">
            <!-- Status Management -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Manage Status</h3>
                <form action="{{ route('tasks.updateStatus', $task) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <select name="status"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                            <option value="todo" {{ $task->status == 'todo' ? 'selected' : '' }}>To Do</option>
                            <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Progress
                            </option>
                            <option value="done" {{ $task->status == 'done' ? 'selected' : '' }}>Done</option>
                        </select>
                    </div>
                    <button type="submit"
                        class="w-full bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-900 transition text-sm font-medium">Update
                        Status</button>
                </form>
            </div>

            <!-- Assignees -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Assigned To</h3>
                <ul class="space-y-3">
                    @foreach ($task->assignees as $assignee)
                        <li class="flex items-center space-x-3">
                            <div
                                class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">
                                {{ substr($assignee->name, 0, 2) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $assignee->name }}</p>
                                <p class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $assignee->role)) }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection

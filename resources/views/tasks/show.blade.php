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

            <!-- Sub-tasks Section -->
            <div class="bg-white shadow rounded-lg overflow-hidden p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Sub-tasks</h3>
                    @if (auth()->user()->isAdmin())
                        <button onclick="document.getElementById('create-subtask-form').classList.toggle('hidden')"
                            class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700 transition">
                            Add Sub-task
                        </button>
                    @endif
                </div>

                <!-- Create Sub-task Form -->
                @if (auth()->user()->isAdmin())
                    <div id="create-subtask-form" class="hidden mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <form action="{{ route('tasks.subtasks.store', $task) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Title <strong
                                            class="text-red-500">*</strong></label>
                                    <input type="text" name="title" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border sm:text-sm">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Priority <strong
                                                class="text-red-500">*</strong></label>
                                        <select name="priority"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border sm:text-sm">
                                            <option value="low">Low</option>
                                            <option value="medium" selected>Medium</option>
                                            <option value="high">High</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Type <strong
                                                class="text-red-500">*</strong></label>
                                        <select name="type"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border sm:text-sm">
                                            <option value="feature">Feature</option>
                                            <option value="bug">Bug</option>
                                            <option value="improvement">Improvement</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Assign To</label>
                                    <select name="assigned_to"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border sm:text-sm">
                                        <option value="">Unassigned</option>
                                        @foreach ($task->assignees as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Attachments
                                        (Mulitple)
                                        <small
                                            class="text-xs text-gray-500">(.jpg,.jpeg,.png,.pdf,.doc,.docx,.txt,.xlsx,.xls,.csv)</small>
                                    </label>
                                    <input type="file" name="attachments[]" multiple
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt,.xlsx,.xls,.csv">
                                </div>
                                <div class="flex justify-end">
                                    <button type="button"
                                        onclick="document.getElementById('create-subtask-form').classList.add('hidden')"
                                        class="mr-2 text-gray-600 hover:text-gray-800 text-sm">Cancel</button>
                                    <button type="submit"
                                        class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700 transition">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif

                <!-- Sub-tasks List -->
                <div class="space-y-3">
                    @forelse ($task->subTasks as $subTask)
                        <div
                            class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 transition {{ $subTask->status == 'done' ? 'bg-green-50' : '' }}">
                            <div class="flex justify-between items-start">
                                <div class="flex-grow min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        @php
                                            $isUpdateStatus = false;
                                            if (auth()->user()->role == 'admin') {
                                                $isUpdateStatus = true;
                                            } else {
                                                $isUpdateStatus = empty($subTask->assigned_to)
                                                    ? true
                                                    : auth()->user()->id == $subTask->assigned_to;
                                            }
                                        @endphp
                                        @if ($isUpdateStatus)
                                            <form action="{{ route('subtasks.update', $subTask) }}" method="POST"
                                                class="inline">
                                                @csrf @method('PUT')
                                                <!-- Update Status Toggle -->
                                                <button type="submit" name="status"
                                                    value="{{ $subTask->status == 'done' ? 'todo' : 'done' }}"
                                                    class="flex items-center gap-2 focus:outline-none group">
                                                    <div
                                                        class="w-5 h-5 rounded border flex items-center justify-center transition-colors {{ $subTask->status == 'done' ? 'bg-green-500 border-green-500' : 'border-gray-400 group-hover:border-indigo-500' }}">
                                                        @if ($subTask->status == 'done')
                                                            <svg class="w-3 h-3 text-white" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="3" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                        @endif
                                                    </div>
                                                </button>
                                            </form>
                                        @endif
                                        <span
                                            class="font-medium text-gray-900 max-w-full overflow-x-auto whitespace-nowrap {{ $subTask->status == 'done' ? 'line-through text-gray-500' : '' }}">
                                            {{ $subTask->title }}
                                        </span>
                                        <span
                                            class="text-xs font-semibold uppercase px-2 py-0.5 rounded {{ $subTask->status == 'done' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $subTask->status == 'done' ? 'Complete' : 'Incomplete' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-4 text-xs text-gray-500 ml-8">
                                        <span
                                            class="px-2 py-0.5 rounded-full {{ $subTask->priority == 'urgent' ? 'bg-red-100 text-red-800' : ($subTask->priority == 'high' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ ucfirst($subTask->priority) }}
                                        </span>
                                        <span
                                            class="bg-purple-50 text-purple-700 px-2 py-0.5 rounded-full">{{ ucfirst($subTask->type) }}</span>
                                        @if ($subTask->assignee)
                                            <span class="flex items-center gap-1">
                                                <div
                                                    class="h-4 w-4 rounded-full bg-indigo-100 flex items-center justify-center text-[10px] font-bold text-indigo-700">
                                                    {{ substr($subTask->assignee->name, 0, 2) }}
                                                </div>
                                                {{ $subTask->assignee->name }}
                                            </span>
                                        @else
                                            <span>Unassigned</span>
                                        @endif
                                    </div>
                                    @if ($subTask->attachments->isNotEmpty())
                                        <div class="mt-2 ml-8">
                                            <p class="text-xs font-semibold text-gray-500 mb-1">Attachments:</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($subTask->attachments as $att)
                                                    <a href="{{ Storage::url($att->file_path) }}" target="_blank"
                                                        class="text-xs text-indigo-600 hover:text-indigo-800 underline bg-indigo-50 px-2 py-1 rounded flex items-center gap-1">
                                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                            </path>
                                                        </svg>
                                                        {{ Str::limit($att->original_name, 20) }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex items-center">
                                    @if (auth()->user()->isAdmin())
                                        <form action="{{ route('subtasks.destroy', $subTask) }}" method="POST"
                                            onsubmit="return confirm('Are you sure?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-red-500 transition">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 italic text-sm text-center py-2">No sub-tasks yet.</p>
                    @endforelse
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
                            $combined = $task->comments
                                ->concat($task->nonAttachableAttachments)
                                ->sortByDesc('created_at');
                            //dump($combined);
                        @endphp

                        @forelse($combined as $item)
                            <div class="flex space-x-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center font-bold text-gray-600">
                                        {{ substr($item->user->name ?? 'U', 0, 2) }}
                                    </div>
                                </div>
                                <div class="flex-grow min-w-0">
                                    <div class="text-sm">
                                        <span
                                            class="font-medium text-gray-900">{{ $item->user->name ?? 'Unknown User' }}</span>
                                        <span class="text-gray-500">
                                            @if ($item instanceof App\Models\Comment)
                                                commented {{ $item->attachments->isNotEmpty() ? 'with attachments' : '' }}
                                            @else
                                                uploaded a file
                                            @endif
                                        </span>
                                        <span class="text-gray-400 mx-1">&middot;</span>
                                        <span class="text-gray-400">{{ $item->created_at->diffForHumans() }}</span>
                                    </div>

                                    @if ($item instanceof App\Models\Comment)
                                        <div
                                            class="mt-1 text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-100 overflow-x-auto whitespace-nowrap max-w-full">
                                            {{ $item->body }}
                                        </div>

                                        @if ($item->attachments->isNotEmpty())
                                            <div class="mt-2 ml-8">
                                                <p class="text-xs font-semibold text-gray-500 mb-1">Attachments:</p>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach ($item->attachments as $attachment)
                                                        <a href="{{ Storage::url($attachment->file_path) }}"
                                                            target="_blank"
                                                            class="text-xs text-indigo-600 hover:text-indigo-800 underline bg-indigo-50 px-2 py-1 rounded flex items-center gap-1">
                                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                                </path>
                                                            </svg>
                                                            {{ $attachment->original_name }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @elseif ($item instanceof App\Models\Attachment)
                                        <div class="mt-1 text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-100">
                                            <div class="flex items-center space-x-2">
                                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                    </path>
                                                </svg>
                                                <a href="{{ Storage::url($item->file_path) }}" target="_blank"
                                                    class="text-indigo-600 hover:text-indigo-800 underline">
                                                    {{ $item->original_name }}
                                                </a>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 italic text-center py-4">No comments or files yet.</p>
                        @endforelse



                    </div>

                    <!-- Add Comment/File Form -->
                    <form action="{{ route('tasks.comments.store', $task) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="body" class="sr-only">Comment</label>
                            <textarea name="body" id="body" rows="3"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-3 border"
                                placeholder="Add a comment... (required)" required></textarea>

                            @error('body')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <label for="attachment"
                                    class="cursor-pointer text-gray-500 hover:text-indigo-600 flex items-center space-x-1">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                    <span class="text-sm">Attach File <small class="text-xs text-gray-500">(only
                                            .jpg,.jpeg,.png,.pdf,.doc,.docx,.txt,.xlsx,.xls,.csv)</small></span>
                                </label>
                                <input type="file" name="attachment" id="attachment" class="hidden"
                                    onchange="document.getElementById('file-name').textContent = this.files[0].name"
                                    accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt,.xlsx,.xls,.csv">
                                <span id="file-name" class="ml-2 text-xs text-gray-500"></span>


                            </div>

                            <button type="submit"
                                class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700 transition text-sm font-medium">
                                Post
                            </button>
                        </div>
                        @error('attachment')
                            <p class="text-red-500 text-xs italic">{{ $message }}</p>
                        @enderror
                    </form>
                </div>

                <!-- Task Updates (Separated) -->
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <h4 class="text-sm font-bold text-gray-700 mb-4 uppercase tracking-wide">System Updates</h4>
                    <div class="space-y-4">
                        @php
                            $combinedLogs = $task->activityLogs
                                ->concat($task->subTasks->flatMap(fn($subTask) => $subTask->activityLogs))
                                ->sortByDesc('created_at');
                        @endphp
                        @forelse($combinedLogs as $log)
                            <div class="text-sm text-gray-600">
                                <span class="font-medium text-gray-800">{{ $log->user->name ?? 'System' }}</span>
                                <span class="inline-block align-middle max-w-full overflow-x-auto whitespace-nowrap">
                                    {{ $log->description }}
                                </span>
                                <span class="text-gray-400 text-xs ml-1">{{ $log->created_at->diffForHumans() }}</span>
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

            <!-- Tags -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Tags</h3>
                <div class="flex flex-wrap gap-2 mb-4">
                    @forelse($task->tags as $tag)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white"
                            style="background-color: {{ $tag->color }}">
                            {{ $tag->name }}
                            <form action="{{ route('tasks.tags.detach', [$task, $tag]) }}" method="POST"
                                class="ml-1">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-white hover:text-gray-200 focus:outline-none">
                                    &times;
                                </button>
                            </form>
                        </span>
                    @empty
                        <span class="text-gray-500 text-sm italic">No tags</span>
                    @endforelse
                </div>

                <form action="{{ route('tasks.tags.attach', $task) }}" method="POST"
                    class="flex items-center space-x-2">
                    @csrf
                    <select name="tag_id"
                        class="flex-grow rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border text-sm">
                        <option value="" disabled selected>Add a tag...</option>
                        @foreach (App\Models\Tag::whereNotIn('id', $task->tags->pluck('id'))->get() as $avalTag)
                            <option value="{{ $avalTag->id }}">{{ $avalTag->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                        class="bg-indigo-600 text-white px-3 py-2 rounded shadow hover:bg-indigo-700 transition text-sm">
                        Add
                    </button>
                </form>

                <div class="mt-4 border-t border-gray-100 pt-4">
                    <p class="text-xs text-gray-500 mb-2">Create New Tag</p>
                    <form action="{{ route('tags.store') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="name" placeholder="Tag Name" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-1.5 border text-xs">
                        <input type="color" name="color" value="#6B7280"
                            class="h-8 w-8 rounded cursor-pointer border border-gray-300 p-0.5">
                        <button type="submit"
                            class="bg-gray-800 text-white px-3 py-1.5 rounded text-xs hover:bg-gray-700">Create</button>
                    </form>
                </div>
            </div>

            <!-- Time Tracking -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Time Tracking</h3>

                @php
                    $activeTimer = $task->timeEntries->where('user_id', Auth::id())->whereNull('end_time')->first();
                    $accumulatedSeconds = $task->timeEntries->whereNotNull('end_time')->sum('duration');
                    // Pass these to JS
                    $isRunning = $activeTimer ? true : false;
                    $startTimeTimestamp = $activeTimer ? $activeTimer->start_time->timestamp : 0;
                @endphp

                <div class="mb-6 text-center">
                    <div id="timer-display" class="text-3xl font-mono font-bold text-gray-800 mb-2">--:--:--</div>
                    <p class="text-xs text-gray-500 uppercase tracking-widest">Total Time Spent</p>
                </div>

                @if ($activeTimer)
                    <div class="mb-6">
                        <div class="bg-green-50 border border-green-200 rounded p-3 text-center mb-3">
                            <span class="text-green-800 font-medium text-sm animate-pulse">
                                Running Since
                                {{ $activeTimer->start_time->setTimezone(Auth::user()->timezone ?? config('app.timezone'))->format('H:i') }}
                            </span>
                        </div>
                        <form action="{{ route('tasks.timer.stop', $task) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full bg-red-600 text-white px-4 py-2 rounded shadow hover:bg-red-700 transition">
                                Stop Timer
                            </button>
                        </form>
                    </div>
                @else
                    <div class="mb-6">
                        <form action="{{ route('tasks.timer.start', $task) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700 transition">
                                Start Timer
                            </button>
                        </form>
                    </div>
                @endif

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const display = document.getElementById('timer-display');
                        const isRunning = @json($isRunning);
                        // Server calculated totals in seconds
                        const accumulatedSeconds = @json($accumulatedSeconds);
                        const initialActiveDuration = @json($activeTimer ? (int) abs(now()->diffInSeconds($activeTimer->start_time)) : 0);

                        let totalSeconds = accumulatedSeconds + initialActiveDuration;

                        function formatTime(sec) {
                            sec = Math.floor(sec); // Ensure we are working with integers
                            const h = Math.floor(sec / 3600);
                            const m = Math.floor((sec % 3600) / 60);
                            const s = sec % 60;
                            return [h, m, s].map(v => v < 10 ? "0" + v : v).join(":");
                        }

                        // Set initial display
                        display.innerText = formatTime(totalSeconds);

                        if (isRunning) {
                            setInterval(() => {
                                totalSeconds++;
                                display.innerText = formatTime(totalSeconds);
                            }, 1000);
                        }
                    });
                </script>
                <div class="border-t border-gray-100 pt-4">
                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-3">Recent Logs</h4>
                    <ul class="space-y-3">
                        @forelse($task->timeEntries->whereNotNull('end_time')->take(5) as $entry)
                            <li class="flex justify-between items-start text-sm">
                                <div>
                                    <span class="block font-medium text-gray-900">{{ $entry->user->name }}</span>
                                    <span
                                        class="text-gray-500 text-xs">{{ $entry->start_time->setTimezone(Auth::user()->timezone ?? config('app.timezone'))->format('M d, H:i') }}</span>
                                </div>
                                <span class="font-mono text-gray-600 bg-gray-100 px-2 py-1 rounded text-xs">
                                    {{ sprintf('%02d:%02d:%02d', floor($entry->duration / 3600), floor(($entry->duration / 60) % 60), $entry->duration % 60) }}
                                </span>
                            </li>
                        @empty
                            <li class="text-gray-400 italic text-xs">No time logged yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

        </div>
    </div>
@endsection

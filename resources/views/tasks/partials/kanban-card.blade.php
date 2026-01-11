<div
    class="bg-white p-4 rounded shadow hover:shadow-md transition border-l-4 
    @if ($task->priority == 'urgent') border-red-500 
    @elseif($task->priority == 'high') border-orange-500 
    @elseif($task->priority == 'medium') border-yellow-500 
    @else border-gray-300 @endif
">
    <div class="flex justify-between items-start mb-2">
        <span class="text-xs px-2 py-0.5 rounded border border-gray-100 bg-gray-50 text-gray-600">
            {{ ucfirst($task->type) }}
        </span>
        @if ($task->due_date)
            <span
                class="text-xs font-semibold @if ($task->due_date < now() && $task->status != 'done') text-red-600 @else text-gray-500 @endif">
                {{ $task->due_date->format('M d') }}
            </span>
        @endif
    </div>

    <div class="flex flex-wrap gap-1 mb-2">
        @foreach ($task->tags as $tag)
            <span class="text-[10px] px-1.5 py-0.5 rounded-full text-white" style="background-color: {{ $tag->color }}">
                {{ $tag->name }}
            </span>
        @endforeach
    </div>

    <a href="{{ route('tasks.show', $task) }}" class="font-bold text-gray-800 hover:text-indigo-600 block mb-1">
        {{ $task->title }}
    </a>
    <div class="flex items-center justify-between mt-3">
        <div class="flex -space-x-1">
            @foreach ($task->assignees->take(2) as $user)
                <div class="w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center text-[10px] ring-2 ring-white"
                    title="{{ $user->name }}">
                    {{ substr($user->name, 0, 2) }}
                </div>
            @endforeach
        </div>

        <div class="flex items-center gap-1">
            <!-- Quick Status Update Actions -->
            <form action="{{ route('tasks.updateStatus', $task) }}" method="POST" class="flex gap-1">
                @csrf
                @if ($task->status != 'todo')
                    <button name="status" value="todo" class="text-gray-400 hover:text-gray-600"
                        title="Move to Todo">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                        </svg>
                    </button>
                @endif
                @if ($task->status != 'in_progress')
                    <button name="status" value="in_progress" class="text-blue-400 hover:text-blue-600"
                        title="Move to In Progress">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </button>
                @endif
                @if ($task->status != 'done')
                    <button name="status" value="done" class="text-green-400 hover:text-green-600"
                        title="Move to Done">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </button>
                @endif
            </form>

            @if (Auth::user()->role === 'admin' || Auth::id() === $task->created_by)
                <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                    onsubmit="return confirm('Delete task?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-300 hover:text-red-500 ml-1" title="Delete Task">
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
</div>

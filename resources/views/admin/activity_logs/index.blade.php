@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Activity Logs</h1>
        <p class="text-gray-600">Audit trail of system actions.</p>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($logs as $log)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $log->user ? $log->user->name : 'System/Guest' }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $log->user ? $log->user->email : '' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $log->description }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if ($log->subject_type)
                                {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $log->ip_address }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $log->created_at->format('M d, Y H:i:s') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No activity logs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
    </div>
@endsection

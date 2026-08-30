<x-admin.app-layout title="Audit Logs">
    <form method="GET" class="flex gap-2 mb-4">
        <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter by action (e.g. updated:User)"
               class="rounded-md border-slate-300 shadow-sm text-sm w-80">
        <button class="rounded-md bg-slate-800 text-white text-sm px-3 py-2">Filter</button>
    </form>

    <x-admin.card>
        <div class="overflow-x-auto -m-5">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3">When</th>
                        <th class="px-5 py-3">Admin</th>
                        <th class="px-5 py-3">Action</th>
                        <th class="px-5 py-3">Changes</th>
                        <th class="px-5 py-3">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-5 py-3 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td class="px-5 py-3">{{ $log->adminUser?->name ?? '—' }}</td>
                            <td class="px-5 py-3"><x-admin.badge color="blue">{{ $log->action }}</x-admin.badge></td>
                            <td class="px-5 py-3 max-w-md">
                                <code class="text-xs text-slate-600 break-all">{{ json_encode($log->new_values) }}</code>
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">No audit activity yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>

    <div class="mt-4">{{ $logs->links() }}</div>
</x-admin.app-layout>

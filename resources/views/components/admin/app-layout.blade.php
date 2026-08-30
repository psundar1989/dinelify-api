@props(['title' => 'Dashboard'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} ·  Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-slate-900 antialiased bg-slate-50" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-30 w-64 bg-slate-900 text-slate-200 transform transition-transform lg:static lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="h-16 flex items-center gap-2 px-5 border-b border-slate-800">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white text-sm font-bold">D</span>
                <span class="font-semibold text-white"> Admin</span>
            </div>
            <nav class="px-3 py-4 space-y-1 text-sm">
                <x-admin.nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Dashboard</x-admin.nav-link>

                @can('manage-users')
                    <x-admin.nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">Users</x-admin.nav-link>
                @endcan

                @can('manage-locations')
                    <x-admin.nav-link :href="route('admin.locations.index')" :active="request()->routeIs('admin.locations.*')">Locations & Rooms</x-admin.nav-link>
                @endcan

                @can('manage-menus')
                    <x-admin.nav-link :href="route('admin.menus.index')" :active="request()->routeIs('admin.menus.*')">Menus</x-admin.nav-link>
                @endcan

                @can('manage-orders')
                    <x-admin.nav-link :href="route('admin.orders.index')" :active="request()->routeIs('admin.orders.*')">Orders</x-admin.nav-link>
                @endcan

                @can('view-reports')
                    <x-admin.nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')">Reports</x-admin.nav-link>
                @endcan

                @can('manage-admin-users')
                    <x-admin.nav-link :href="route('admin.admin-users.index')" :active="request()->routeIs('admin.admin-users.*')">Admin Users</x-admin.nav-link>
                @endcan

                @can('manage-roles')
                    <x-admin.nav-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.*')">Roles & Permissions</x-admin.nav-link>
                @endcan

                @can('view-audit-logs')
                    <x-admin.nav-link :href="route('admin.audit-logs.index')" :active="request()->routeIs('admin.audit-logs.*')">Audit Logs</x-admin.nav-link>
                @endcan
            </nav>
        </aside>

        <div class="flex-1 flex flex-col lg:pl-0" :class="sidebarOpen ? 'lg:pl-0' : ''">
            <!-- Topbar -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 lg:px-6">
                <button class="lg:hidden text-slate-600" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
                <h1 class="text-base font-semibold text-slate-800">{{ $title }}</h1>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-slate-500 hidden sm:inline">{{ auth('admin')->user()->name }}</span>
                    <span class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                        {{ auth('admin')->user()->getRoleNames()->first() ?? 'admin' }}
                    </span>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-slate-500 hover:text-red-600">Log out</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-4 lg:p-6">
                @if (session('status'))
                    <div class="mb-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-md px-3 py-2">
                        {{ session('status') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>

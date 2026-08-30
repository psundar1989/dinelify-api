<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dinelify Admin' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-slate-900 antialiased bg-slate-100">
    <div class="min-h-screen flex flex-col justify-center items-center px-4">
        <div class="mb-6 flex items-center gap-2">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white text-lg font-bold">D</span>
            <span class="text-xl font-semibold text-slate-800"> Admin</span>
        </div>
        <div class="w-full sm:max-w-md bg-white shadow-sm ring-1 ring-slate-200 rounded-xl px-6 py-8">
            {{ $slot }}
        </div>
    </div>
</body>
</html>

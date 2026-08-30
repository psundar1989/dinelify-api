@props(['title' => null])
<div {{ $attributes->merge(['class' => 'bg-white rounded-xl ring-1 ring-slate-200 shadow-sm']) }}>
    @if ($title)
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-sm font-semibold text-slate-700">{{ $title }}</h2>
        </div>
    @endif
    <div class="p-5">
        {{ $slot }}
    </div>
</div>

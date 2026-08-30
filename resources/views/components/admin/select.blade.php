@props(['label', 'name', 'options' => [], 'selected' => null, 'placeholder' => null, 'required' => false])
<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>
    <select id="{{ $name }}" name="{{ $name }}" @if($required) required @endif
            {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm']) }}>
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" @selected((string) old($name, $selected) === (string) $optValue)>{{ $optLabel }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

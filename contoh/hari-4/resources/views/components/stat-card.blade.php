@props(['icon', 'label', 'value', 'color' => 'green'])

@php
    $bgColor   = "bg-{$color}-50";
    $iconColor = "text-{$color}-600";
    $ringColor = "ring-{$color}-100";
@endphp

<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center">
        <div class="flex-shrink-0 p-3 rounded-full {{ $bgColor }} ring-4 {{ $ringColor }}">
            <span class="text-2xl {{ $iconColor }}">{!! $icon !!}</span>
        </div>
        <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ $value }}</p>
        </div>
    </div>
</div>

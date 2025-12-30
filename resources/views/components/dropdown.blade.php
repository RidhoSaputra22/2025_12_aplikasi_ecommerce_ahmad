@props([
'align' => 'right', // left|right
'width' => 'auto', // auto|min-w-48|min-w-64|min-w-2xl

'contentClasses' => 'bg-white border border-gray-300 rounded-e-lt shadow-md',
'open' => false, // initial state
])

@php
$alignmentClasses = match ($align) {
'left' => 'origin-top-left left-0',
default => 'origin-top-right right-0',
};

@endphp

<div {{ $attributes->merge(['class' => 'relative']) }} x-data="{ open: @js($open) }"
    @keydown.escape.window="open = false">
    <div @click="open = !open" @click.outside="open = false" class="inline-flex">
        <button type="button" class="inline-flex items-center gap-1 cursor-pointer">
            {{ $trigger ?? '' }}

            <svg class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.25a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    <div x-cloak x-show="open" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 rounded-md shadow-lg {{ $width }} {{ $alignmentClasses }}"
        style="display: none;">
        <div class="rounded-md ring-1 ring-black/5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>

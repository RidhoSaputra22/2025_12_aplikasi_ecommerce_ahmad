{{-- resources/views/components/spinner.blade.php --}}
@props([
'size' => 24, // px
'stroke' => 3, // px
'class' => 'text-primary',
'label' => 'Loading...',
])

<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="{{ $size }}" height="{{ $size }}" fill="none"
    class="animate-spin {{ $class }}" role="status" aria-live="polite" aria-label="{{ $label }}">
    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="{{ $stroke }}" opacity="0.25" />
    <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="{{ $stroke }}" stroke-linecap="round"
        opacity="0.9" />
</svg>

<div class="input-form {{ $class ?? '' }}">
    <label for="{{ $name }}">{{ $label }}</label>
    <input type="{{ $type ?? 'text' }}" name="{{ $name }}" id="{{ $name }}">
</div>

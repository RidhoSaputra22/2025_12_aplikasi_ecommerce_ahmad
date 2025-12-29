<div class="input-form {{ $class ?? '' }}">
    <label for="{{ $name }}">{{ $label }}</label>
    <textarea name="{{ $name }}" id="{{ $name }}" cols="{{ $cols ?? '30' }}" rows="{{ $rows ?? '4' }}"></textarea>

</div>

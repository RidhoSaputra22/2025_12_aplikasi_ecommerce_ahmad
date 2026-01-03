@props([
    'wireModel' => null,
    'label' => null,
    'cols' => 30,
    'rows' => 4,
    'class' => '',
    'value' => null,

])

<div class="input-form {{ $class }}">
    @if($label)
        <label for="{{ $wireModel }}">{{ $label }}</label>
    @endif

    <textarea
        {{ $attributes->merge([
            'name' => $wireModel,
            'id' => $wireModel,
            'cols' => $cols,
            'rows' => $rows,
        ]) }}
        wire:model="{{ $wireModel }}"
    ></textarea>
</div>

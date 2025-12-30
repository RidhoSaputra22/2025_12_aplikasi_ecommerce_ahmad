@props([
    'label',
    'wireModel' => null,
    'options' => [],
    'selected' => null,
    'default' => null,
    'class' => null,
])

<div class="input-form">
    <label for="{{ $wireModel }}">{{ $label }}</label>
    <select wire:model.live="{{ $wireModel }}" id="{{ $wireModel }}" class="{{$class ?? ''}}">
        @if (isset($default))
        <option value="{{ $default['value'] }}"
            {{ (isset($selected) && $selected == $default['value']) ? 'selected' : '' }}>{{ $default['label'] }}
        </option>
        @endif
        @foreach ($options as $option)
        <option value="{{ $option['value'] }}"
            {{ (isset($selected) && $selected == $option['value']) ? 'selected' : '' }}>{{ $option['label'] }}</option>
        @endforeach
    </select>
</div>

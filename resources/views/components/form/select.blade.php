<div class="input-form">
    <label for="{{ $name }}">{{ $label }}</label>
    <select name="{{ $name }}" id="{{ $name }}" class="{{$class ?? ''}}">
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

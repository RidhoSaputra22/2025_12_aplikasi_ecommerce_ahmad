@props([
    'label' => '',
    'wireModel',
    'type' => 'text',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'class' => '',
])

@php
$type = $type ?? 'text';
// id yang aman kalau wire:model berisi titik (.)
$inputId = str_replace('.', '_', $wireModel);
@endphp

<div class="input-form {{ $class ?? '' }}">
    <label for="{{ $inputId }}">{{ $label }}</label>

    @if($type === 'password')
    <div class="relative">
        <input type="password" wire:model="{{ $wireModel }}" id="{{ $inputId }}" name="{{ $wireModel }}"
            placeholder="{{ $placeholder ?? '' }}" autocomplete="current-password"
            {{ isset($required) && $required ? 'required' : '' }} class="pr-12" {{ isset($disabled) && $disabled ? 'readonly' : '' }}>

        <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 p-1"
            aria-label="Toggle password visibility"
            onclick="(function(btn){const root=btn.closest('div');const i=root&&root.querySelector('input');if(!i)return;i.type=i.type==='password'?'text':'password';})(this)">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                <circle cx="12" cy="12" r="3" />
            </svg>
        </button>
    </div>
    @else
    <input type="{{ $type }}" wire:model="{{ $wireModel }}" id="{{ $inputId }}" name="{{ $wireModel }}"
        placeholder="{{ $placeholder ?? '' }}"
        class="{{ isset($disabled) && $disabled ? '!bg-gray-200 cursor-not-allowed' : '' }}"
        {{ isset($required) && $required ? 'required' : '' }} {{ isset($disabled) && $disabled ? 'readonly' : '' }}>
    @endif

    @error($wireModel)
    <p class="text-red-500 text-sm font-light">{{ $message }}</p>
    @enderror
</div>

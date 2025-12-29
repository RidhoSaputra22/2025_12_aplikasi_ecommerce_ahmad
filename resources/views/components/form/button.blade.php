@props([
'label' => 'Submit',
'class' => '',
'wireClick' => null,
'wireLoadingTarget' => null,
'wireLoadingClass' => null,
])

@php
$loadingTarget = $wireLoadingTarget ?? $wireClick;
@endphp

<div class="input-form ">
    <button type="submit"
        @if ($wireClick) wire:click="{{ $wireClick }}" @endif
        @if ($loadingTarget) wire:loading.attr="disabled" wire:target="{{ $loadingTarget }}" @endif
        @if ($wireLoadingClass) wire:loading.class="{{ $wireLoadingClass }}" @endif
        class="{{ $class ?? '' }}">

        <span wire:loading @if ($loadingTarget) wire:target="{{ $loadingTarget }}" @endif
            class="inline -block mr-2">@include('components.spinner')</span>
        <span wire:loading.remove @if ($loadingTarget) wire:target="{{ $loadingTarget }}" @endif>{{ $label }}</span>
    </button>
</div>
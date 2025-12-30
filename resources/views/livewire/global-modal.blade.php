<div>
    @php($active = $this->activeModal)

    @if ($active)
        <div
            wire:key="global-modal-{{ $active['id'] }}"
            x-data="{
                open: false,
                close() {
                    if (!this.open) return;
                    this.open = false;
                    setTimeout(() => { $wire.closeModal(); }, 200);
                }
            }"
            x-init="$nextTick(() => { open = true })"
            x-cloak
            x-on:keydown.escape.window="if (@js($active['closeOnEscape'] ?? true)) close()"
        >
            <div class="fixed inset-0 z-50" aria-live="polite">
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute inset-0 bg-black/50"
                    @if ($active['closeOnBackdrop'] ?? true)
                        x-on:click="close()"
                    @endif
                ></div>

                <div class="relative flex min-h-full items-center justify-center p-4">
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                        class="w-full {{ $active['maxWidthClass'] ?? 'max-w-7xl' }} rounded-lg bg-white"
                        x-on:click.stop
                    >
                    <div class="flex items-center justify-between border-b p-4">
                        <h2 class="text-xl/normal font-semibold uppercase">{{ $active['title'] ?? '' }}</h2>

                        <button
                            type="button"
                            class="cursor-pointer inline-flex items-center justify-center rounded-md px-2 py-1 text-sm"
                            x-on:click="close()"
                            aria-label="Tutup"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="p-4">
                        @if (($active['type'] ?? null) === 'component' && !empty($active['component']))
                            @livewire($active['component'], $active['arguments'] ?? [], key('modal-'.$active['id']))
                        @elseif (($active['type'] ?? null) === 'view' && !empty($active['view']))
                            @include($active['view'], $active['data'] ?? [])
                        @elseif (($active['type'] ?? null) === 'html' && !empty($active['html']))
                            {!! $active['html'] !!}
                        @else
                            <p class="text-sm text-gray-600">Konten belum diset.</p>
                        @endif
                    </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

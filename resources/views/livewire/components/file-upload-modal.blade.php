<div
    x-data="{ uploading: false, progress: 0, finished: false, isDragging: false }"
    x-on:livewire-upload-start="uploading = true; finished = false; progress = 0"
    x-on:livewire-upload-progress="progress = $event.detail.progress"
    x-on:livewire-upload-finish="uploading = false; finished = true"
    x-on:livewire-upload-error="uploading = false; finished = false"
>
    <style>
        @keyframes pop {
            0% { transform: scale(.85); }
            100% { transform: scale(1); }
        }
    </style>
    <form wire:submit.prevent="save" class="space-y-4">
        <input
            x-ref="input"
            id="file_input"
            type="file"
            wire:model="file"
            @if (!empty($accept)) accept="{{ $accept }}" @endif
            class="hidden"
        />

        @php
            $fileName = $file && method_exists($file, 'getClientOriginalName') ? (string) $file->getClientOriginalName() : null;
            $fileSize = $file && method_exists($file, 'getSize') ? (int) $file->getSize() : null;
            $fileSizeLabel = null;
            if ($fileSize !== null) {
                $kb = (int) round($fileSize / 1024);
                $fileSizeLabel = $kb >= 1024 ? (number_format($kb / 1024, 1) . ' MB') : ($kb . ' KB');
            }
        @endphp

        <label for="file_input" class="block">
            <div
                class="relative rounded-xl border border-gray-200 bg-white overflow-hidden transition-colors duration-200"
                x-bind:class="isDragging ? 'border-primary ring-2 ring-primary/20' : ''"
                x-on:dragover.prevent
                x-on:dragenter.prevent="isDragging = true"
                x-on:dragleave.prevent.self="isDragging = false"
                x-on:drop.prevent="
                    isDragging = false;
                    if ($event.dataTransfer?.files?.length) {
                        uploading = true; finished = false; progress = 0;
                        $refs.input.files = $event.dataTransfer.files;
                        $refs.input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                "
            >
                <div
                    x-show="isDragging"
                    x-transition.opacity
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute inset-0 z-10 flex items-center justify-center bg-black/50"
                    aria-hidden="true"
                >
                    <div
                        class="rounded-lg bg-white/95 px-4 py-2 text-sm font-semibold text-gray-800 transform transition-transform duration-200"
                        x-bind:class="isDragging ? 'scale-100' : 'scale-95'"
                    >
                        Lepaskan file untuk mengunggah
                    </div>
                </div>

                @if ($file)
                    <div class="relative">
                        <div class="aspect-16/6 bg-gray-100">
                            @if ($imageOnly)
                                <img src="{{ $file->temporaryUrl() }}" alt="Preview" class="w-full h-full object-contain bg-black/95 transition-transform duration-300" />
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-600 text-sm">
                                    {{ $fileName ?? 'File dipilih' }}
                                </div>
                            @endif
                        </div>

                        <div class="absolute inset-x-0 top-0 p-4 text-white">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="text-sm font-semibold">{{ Str::limit($fileName ?? 'File', 30) }}</div>
                                    @if ($fileSizeLabel)
                                        <div class="text-xs opacity-80">{{ $fileSizeLabel }}</div>
                                    @endif
                                </div>



                                <template x-if="!uploading && finished">
                                    <div class="flex items-center justify-end gap-2">
                                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-green-500/90 animate-[pop_.25s_ease-out]">
                                            <svg class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.07 7.093a1 1 0 0 1-1.42 0L3.29 8.87a1 1 0 1 1 1.414-1.415l3.81 3.81 6.364-6.38a1 1 0 0 1 1.416.006Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>


                                    </div>
                                </template>



                            </div>


                        </div>

                        <div
                            class="absolute inset-x-0 top-0 h-12"

                        ></div>
                    </div>
                @else
                    <div class="p-6">
                        <div class="rounded-lg border border-dashed border-gray-300 p-6 text-center">
                            <div class="text-sm text-gray-700">
                                Seret &amp; Jatuhkan berkas Anda atau
                                <span class="text-primary font-semibold">Jelajahi</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </label>

        <div x-show="uploading && !{{ $file ? 'true' : 'false' }}" class="-mt-2">
            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                <div class="h-full rounded-full bg-primary transition-[width] duration-200 ease-out" x-bind:style="`width: ${progress}%;`"></div>
            </div>
            <div class="mt-1 text-xs text-gray-600">
                Mengunggah <span x-text="progress"></span>%
            </div>
        </div>

        @error('file')
            <div class="text-xs text-red-600">{{ $message }}</div>
        @enderror

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="save"
            class="w-full bg-primary text-white py-3 px-4 rounded-lg text-sm font-semibold hover:opacity-90 cursor-pointer"
            @if (!$file) disabled @endif
            @if (!$file) style="opacity:.7;cursor:not-allowed" @endif
        >
            <span wire:loading.remove wire:target="save">Upload</span>
            <span wire:loading wire:target="save">Mengunggah...</span>
        </button>
    </form>
</div>

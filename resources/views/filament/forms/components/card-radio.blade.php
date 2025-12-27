{{-- {{dd($getState() )}} --}}


<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{ state: $wire.$entangle(@js($getStatePath())) }" class="space-y-3">

        @foreach ($getOptions() as $value => $label)
        @php
        $description = $getDescriptions()[$value] ?? null;

        @endphp

        <label @click="state = @js($value)"
            class="flex items-start justify-between gap-4 p-4 border rounded-xl cursor-pointer transition" :class="state == @js($value)
                    ? 'border-primary-600 bg-primary-50'
                    : 'border-gray-300 bg-white hover:border-gray-400'">
            {{-- Hidden native radio --}}
            <input type="radio" :value="@js($value)" x-model="state" class="sr-only" />

            {{-- Left content --}}
            <div class="space-y-1">
                <div class="font-medium text-gray-900">
                    {{ $label }}
                </div>


                @if ($description)
                <div class="text-sm text-gray-500">
                    {{ $description }}
                </div>
                @endif
            </div>

            {{-- Right indicator --}}
            <div class="mt-1 flex h-5 w-5 items-center justify-center rounded-full border" :class="state == @js($value)
                        ? 'border-primary-600'
                        : 'border-gray-300'">
                <div class="h-2.5 w-2.5 rounded-full bg-primary-600" x-show="state == @js($value)" x-transition></div>
            </div>
        </label>

        @endforeach
    </div>
</x-dynamic-component>
<?php

namespace App\Livewire;

use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class GlobalModal extends Component
{
    /**
     * Stack of opened modals (supports nested/child modals).
     * Each item:
     *  - id: string
     *  - type: 'component' | 'view' | 'html'
     *  - title: string
     *  - component?: string
     *  - arguments?: array
     *  - view?: string
     *  - data?: array
     *  - html?: string
     *  - closeOnBackdrop: bool
     *  - closeOnEscape: bool
     *  - maxWidthClass: string
     */
    public array $modals = [];

    public function getIsOpenProperty(): bool
    {
        return !empty($this->modals);
    }

    public function getActiveModalProperty(): ?array
    {
        if (empty($this->modals)) {
            return null;
        }

        return $this->modals[array_key_last($this->modals)] ?? null;
    }

    protected function maxWidthClass(string $maxWidth): string
    {
        return match ($maxWidth) {
            'sm' => 'max-w-sm',
            'md' => 'max-w-md',
            'lg' => 'max-w-lg',
            'xl' => 'max-w-xl',
            '2xl' => 'max-w-2xl',
            '3xl' => 'max-w-3xl',
            '4xl' => 'max-w-4xl',
            '5xl' => 'max-w-5xl',
            '6xl' => 'max-w-6xl',
            '7xl' => 'max-w-7xl',
            'full' => 'max-w-full',
            default => 'max-w-2xl',
        };
    }

    protected function pushModal(array $payload): void
    {
        $this->resetErrorBag();

        $id = (string) Str::uuid();
        $maxWidth = (string) ($payload['maxWidth'] ?? '7xl');

        $this->modals[] = [
            'id' => $id,
            'type' => (string) ($payload['type'] ?? 'component'),
            'title' => (string) ($payload['title'] ?? ''),
            'component' => $payload['component'] ?? null,
            'arguments' => (array) ($payload['arguments'] ?? []),
            'view' => $payload['view'] ?? null,
            'data' => (array) ($payload['data'] ?? []),
            'html' => $payload['html'] ?? null,
            'closeOnBackdrop' => (bool) ($payload['closeOnBackdrop'] ?? true),
            'closeOnEscape' => (bool) ($payload['closeOnEscape'] ?? true),
            'maxWidthClass' => $this->maxWidthClass($maxWidth),
        ];
    }

    #[On('openModal')]
    public function openModal(
        string $component,
        array $arguments = [],
        string $title = '',
        string $maxWidth = '7xl',
        bool $closeOnBackdrop = true,
        bool $closeOnEscape = true,
    ): void {
        $this->pushModal([
            'type' => 'component',
            'title' => $title,
            'component' => $component,
            'arguments' => $arguments,
            'maxWidth' => $maxWidth,
            'closeOnBackdrop' => $closeOnBackdrop,
            'closeOnEscape' => $closeOnEscape,
        ]);
    }

    #[On('closeModal')]
    public function closeModal(?string $id = null): void
    {
        if (empty($this->modals)) {
            return;
        }

        if ($id === null) {
            array_pop($this->modals);
            return;
        }

        // Remove the given modal and any modals opened above it.
        for ($i = count($this->modals) - 1; $i >= 0; $i--) {
            $current = $this->modals[$i] ?? null;
            array_pop($this->modals);
            if (($current['id'] ?? null) === $id) {
                break;
            }
        }
    }

    #[On('closeAllModals')]
    public function closeAllModals(): void
    {
        $this->modals = [];
    }

    // Alias: allow child modals to force-close the entire stack.
    #[On('forceCloseModal')]
    #[On('global-modal:force-close')]
    public function forceCloseModal(): void
    {
        $this->closeAllModals();
    }

    // Legacy support (for previous implementation)
    #[On('global-modal:open')]
    public function open(
        string $title = '',
        ?string $view = null,
        array $data = [],
        ?string $html = null,
        bool $closeOnBackdrop = true,
        bool $closeOnEscape = true,
        string $maxWidth = '7xl',
    ): void {
        $type = $view ? 'view' : ($html ? 'html' : 'view');

        $this->pushModal([
            'type' => $type,
            'title' => $title,
            'view' => $view,
            'data' => $data,
            'html' => $html,
            'maxWidth' => $maxWidth,
            'closeOnBackdrop' => $closeOnBackdrop,
            'closeOnEscape' => $closeOnEscape,
        ]);
    }

    #[On('global-modal:close')]
    public function close(): void
    {
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.global-modal');
    }
}

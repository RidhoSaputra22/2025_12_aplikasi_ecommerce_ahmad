<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class FileUploadModal extends Component
{
    use WithFileUploads;

    private const UPLOAD_PROFILES = [
        'profile-photo:uploaded' => [
            'accept' => 'image/*',
        ],
        'vendor-logo:uploaded' => [
            'accept' => 'image/*',
        ],
    ];

    public $file;

    public string $disk = 'public';

    public string $directory = 'uploads';

    public string $accept = '';

    public int $maxSizeKb = 2048;

    public bool $imageOnly = false;

    /**
     * Event name yang akan dipancarkan setelah upload sukses.
     * Payload: path, originalName
     */
    public string $returnEvent = 'file-uploaded';

    public function mount(
        string $disk = 'public',
        string $directory = 'uploads',
        string $accept = '',
        int $maxSizeKb = 2048,
        bool $imageOnly = false,
        string $returnEvent = 'file-uploaded',
    ): void {
        abort_unless(Auth::check(), 403);

        $profile = $this->resolveUploadProfile($returnEvent);
        abort_unless($profile, 403);

        $this->disk = 'public';
        $this->directory = $profile['directory'];
        $this->accept = $profile['accept'];
        $this->maxSizeKb = 2048;
        $this->imageOnly = true;
        $this->returnEvent = $returnEvent;
    }

    public function save(): void
    {
        if (! Auth::check()) {
            return;
        }

        // Resolve the trusted profile again because Livewire public properties
        // can be changed by a crafted client request after mount.
        $profile = $this->resolveUploadProfile($this->returnEvent);
        abort_unless($profile, 403);

        $this->disk = 'public';
        $this->directory = $profile['directory'];
        $this->maxSizeKb = 2048;
        $this->imageOnly = true;

        $rules = ['required', 'file', 'image', 'max:2048'];

        $validated = $this->validate([
            'file' => $rules,
        ], [
            'file.required' => 'File wajib dipilih.',
            'file.file' => 'Input tidak valid.',
            'file.image' => 'File harus berupa gambar.',
            'file.max' => 'Ukuran file terlalu besar.',
        ]);

        $uploaded = $validated['file'];
        $originalName = method_exists($uploaded, 'getClientOriginalName') ? (string) $uploaded->getClientOriginalName() : '';

        $path = $uploaded->store($this->directory, $this->disk);

        $this->dispatch($this->returnEvent, path: $path, originalName: $originalName);
        $this->dispatch('forceCloseModal');
    }

    public function clear(): void
    {
        $this->reset('file');
        $this->resetErrorBag('file');
    }

    public function render()
    {
        return view('livewire.components.file-upload-modal');
    }

    private function resolveUploadProfile(string $returnEvent): ?array
    {
        $profile = self::UPLOAD_PROFILES[$returnEvent] ?? null;
        if (! $profile || ! Auth::check()) {
            return null;
        }

        if ($returnEvent === 'profile-photo:uploaded') {
            $profile['directory'] = 'users/'.Auth::id().'/photos';

            return $profile;
        }

        $vendorId = Auth::user()?->vendor?->id;
        if ($returnEvent === 'vendor-logo:uploaded' && $vendorId) {
            $profile['directory'] = 'vendors/'.$vendorId.'/logos';

            return $profile;
        }

        return null;
    }
}

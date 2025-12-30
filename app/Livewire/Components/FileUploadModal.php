<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FileUploadModal extends Component
{
    use WithFileUploads;

    public $file ;

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
        $this->disk = $disk;
        $this->directory = trim($directory, '/');
        $this->accept = $accept;
        $this->maxSizeKb = $maxSizeKb;
        $this->imageOnly = $imageOnly;
        $this->returnEvent = $returnEvent;



    }

    public function save(): void
    {
        if (!Auth::check()) {
            return;
        }

        $rules = ['required', 'file', 'max:' . $this->maxSizeKb];
        if ($this->imageOnly) {
            $rules[] = 'image';
        }

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
}

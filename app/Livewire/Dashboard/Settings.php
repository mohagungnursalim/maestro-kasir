<?php

namespace App\Livewire\Dashboard;

use App\Models\StoreSetting;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Settings extends Component
{
    use WithFileUploads;

    public $store_name;
    public $store_address;
    public $store_phone;
    public $store_logo;
    public $store_footer;
    public $new_logo;

    public function mount()
    {
        $settings = StoreSetting::first();

        $this->store_name = $settings->store_name;
        $this->store_address = $settings->store_address;
        $this->store_phone = $settings->store_phone;
        $this->store_logo = $settings->store_logo;
        $this->store_footer = $settings->store_footer;
    }

    public function updateSettings()
    {
        $this->validate([
            'store_name' => 'nullable|string|max:80',
            'store_address' => 'nullable|string|max:100',
            'store_phone' => 'nullable|digits_between:10,14',
            'store_footer' => 'nullable|string|max:100',
            'new_logo' => 'nullable|image|max:2048',
        ], [
            'store_name.max' => 'Nama toko tidak boleh lebih dari 80 karakter.',
            'store_address.max' => 'Alamat toko tidak boleh lebih dari 100 karakter.',
            'store_phone.digits_between' => 'Nomor telepon harus terdiri dari 10 hingga 14 digit.',
            'store_footer.max' => 'Footer struk tidak boleh lebih dari 100 karakter.',
            'new_logo.image' => 'File yang diunggah harus berupa gambar.',
            'new_logo.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.'
        ]);
        
    
        $settings = StoreSetting::firstOrNew([]);
    
        // Handle logo upload
        if ($this->new_logo) {
            // Simpan gambar baru
            $imagePath = $this->new_logo->store('logos', 'public');
    
            // Hapus gambar lama jika ada
            if (!empty($settings->store_logo)) {
                $oldLogoPath = str_replace('storage/', '', $settings->store_logo);
                if (Storage::disk('public')->exists($oldLogoPath)) {
                    Storage::disk('public')->delete($oldLogoPath);
                }
            }
    
            // Simpan path baru
            $settings->store_logo = "storage/{$imagePath}";
        }
    
        // Update store settings
        $settings->store_name = $this->store_name ?? 'Default Name';
        $settings->store_address = $this->store_address ?? 'Default Address';
        $settings->store_phone = $this->store_phone ?? '085756000000';
        $settings->store_footer = $this->store_footer ?? 'Default Footer';
        $settings->save();
    
        session()->flash('success', 'Pengaturan berhasil diperbarui!');
        return $this->redirect('/dashboard/store-settings', navigate: true);
    }

    public function render()
    {
        return view('livewire.dashboard.settings');
    }
}

<?php

namespace App\Livewire\Dashboard;

use App\Models\StoreSetting;
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

        $this->store_name = $settings->store_name ?? 'Default Name';
        $this->store_address = $settings->store_address ?? 'Default Address';
        $this->store_phone = $settings->store_phone ?? '085756000000';
        $this->store_logo = $settings->store_logo ?? 'Default Logo';
        $this->store_footer = $settings->store_footer ?? 'Default Footer';
    }

    public function updateSettings()
    {
        $this->validate([
            'store_name' => 'nullable|string|max:80',
            'store_address' => 'nullable|string|max:100',
            'store_phone' => 'nullable|digits_between:10,14',
            'store_footer' => 'nullable|string|max:100',
            'new_logo' => 'nullable|image|max:2048',
        ]);

        $settings = StoreSetting::firstOrNew([]);

        // Handle logo upload
        if ($this->new_logo) {
            $imagePath = $this->new_logo->store('logos', 'public');

            if (!empty($settings->store_logo) && file_exists(public_path('storage/' . $settings->store_logo))) {
                @unlink(public_path('storage/' . $settings->store_logo));
            }

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

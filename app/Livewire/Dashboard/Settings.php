<?php

namespace App\Livewire\Dashboard;

use App\Models\StoreSetting;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\StoreSetting as ModelsStoreSetting;

class Settings extends Component
{
    use WithFileUploads;

    public $store_name;
    public $store_logo;
    public $store_footer;
    public $new_logo;

    public function mount()
    {
        $settings = StoreSetting::first();
        $this->store_name = $settings->store_name;
        $this->store_logo = $settings->store_logo;
        $this->store_footer = $settings->store_footer;
    }

    public function updateSettings()
    {
        $this->validate([
            'store_name' => 'required|string|max:255',
            'store_footer' => 'required|string|max:255',
            'new_logo' => 'nullable|image|max:2048',
        ]);

        $settings = StoreSetting::first();

        if ($this->new_logo) {
            $imagePath = $this->new_logo->store('logos', 'public');

            if ($settings->store_logo && file_exists(public_path('storage/' . $settings->store_logo))) {
                unlink(public_path('storage/' . $settings->store_logo));
            }

            $settings->store_logo = "/storage/{$imagePath}";
        }

        $settings->store_name = $this->store_name;
        $settings->store_footer = $this->store_footer;
        $settings->save();

        $this->dispatch('successUpdate');
        return $this->redirect('/dashboard/store-settings', navigate: true);
    }

    public function render()
    {
        return view('livewire.dashboard.settings');
    }
}

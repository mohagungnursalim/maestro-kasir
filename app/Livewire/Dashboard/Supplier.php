<?php

namespace App\Livewire\Dashboard;

use App\Models\Supplier as ModelsSupplier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class Supplier extends Component
{
    // Store
    public $name, $email, $phone, $address, $city, $state, $zip, $country;

    // Update
    public $supplierId, $nameUpdate, $emailUpdate, $phoneUpdate, $addressUpdate, $cityUpdate, $stateUpdate, $zipUpdate, $countryUpdate;

    // List Supplier
    public $suppliers, $totalSuppliers;

    // Search
    #[Url()]
    public $search = '';
    public $limit = 8; 
    public $loaded = false;

    public $ttl = 31536000;

    protected $listeners = [
        'supplierUpdated' => 'loadInitialSuppliers',
        'deleteConfirmed' => 'delete',
    ];

    public $cacheKeysKey = 'supplier_cache_keys';

    public function mount()
    {
        $this->totalSuppliers = Cache::remember('totalSuppliers', $this->ttl, function () {
            return ModelsSupplier::count();
        });
         
        $this->suppliers = collect();
    }

    public function updatingSearch()
    {
        $this->limit = 8;
    }

    public function updatedSearch()
    {
        $this->loadInitialSuppliers();
    }

    public function loadInitialSuppliers()
    {
        $this->loaded = true;
    
        $cacheKey = "suppliers_{$this->search}_{$this->limit}";

        $this->suppliers = Cache::remember($cacheKey, $this->ttl, function () {
            return ModelsSupplier::where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('address', 'like', '%' . $this->search . '%')
                        ->orWhere('city', 'like', '%' . $this->search . '%')
                        ->orWhere('state', 'like', '%' . $this->search . '%')
                        ->orWhere('zip', 'like', '%' . $this->search . '%')
                        ->orWhere('country', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->take($this->limit)
                ->get();
        });

        // Tambahkan cache key ke list tracking
        $existingKeys = Cache::get($this->cacheKeysKey, []);
        if (!in_array($cacheKey, $existingKeys)) {
            $existingKeys[] = $cacheKey;
            Cache::put($this->cacheKeysKey, $existingKeys, $this->ttl);
        }

    }
    

    public function loadMore()
    {
        $this->limit += 8;
        $this->loadInitialSuppliers();
    }
    

    #[On('resetForm')]
    public function resetForm()
    {
        $this->reset(['name', 'email', 'phone', 'address', 'city', 'state', 'zip', 'country']);
    }

    #[on('resetFormEdit')]
    public function resetFormEdit()
    {
        $this->reset(['nameUpdate', 'emailUpdate', 'phoneUpdate', 'addressUpdate', 'cityUpdate', 'stateUpdate', 'zipUpdate', 'countryUpdate']);
    }

    // CRUD Supplier
    public function store()
    {
        $this->validate([
            'name' => 'required',
            'email' => 'nullable|email|unique:suppliers,email',
            'phone' => 'nullable|max:15',
            'address' => 'nullable|max:100',
            'city' => 'nullable|max:50',
            'state' => 'nullable|max:50',
            'zip' => 'nullable|max:10',
            'country' => 'nullable|max:20',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'phone.max' => 'Nomor telepon maksimal 15 karakter.',
            'address.max' => 'Alamat maksimal 100 karakter.',
            'city.max' => 'Kota maksimal 50 karakter.',
            'state.max' => 'Provinsi maksimal 50 karakter.',
            'zip.max' => 'Kode pos maksimal 10 karakter.',
            'country.max' => 'Negara maksimal 20 karakter.',
        ]);
        
        

        ModelsSupplier::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'country' => $this->country,
        ]);

        // Refresh cache setelah ditambahkan
        $this->refreshCache();

        $this->dispatch('supplierUpdated');
        $this->dispatch('addedSuccess');
    }

    public function editModal($id)
    {
        $supplier = ModelsSupplier::find($id);
        $this->supplierId = $id;
        $this->nameUpdate = $supplier->name;
        $this->emailUpdate = $supplier->email;
        $this->phoneUpdate = $supplier->phone;
        $this->addressUpdate = $supplier->address;
        $this->cityUpdate = $supplier->city;
        $this->stateUpdate = $supplier->state;
        $this->zipUpdate = $supplier->zip;
        $this->countryUpdate = $supplier->country;

        $this->dispatch('showEditModal');
    }

    public function update()
    {
        $this->validate([
            'nameUpdate' => 'required',
            'emailUpdate' => 'nullable|email|unique:suppliers,email,' . $this->supplierId,
            'phoneUpdate' => 'nullable|max:15',
            'addressUpdate' => 'nullable|max:100',
            'cityUpdate' => 'nullable|max:50',
            'stateUpdate' => 'nullable|max:50',
            'zipUpdate' => 'nullable|max:10',
            'countryUpdate' => 'nullable|max:20',
        ], [
            'nameUpdate.required' => 'Nama wajib diisi.',
            'emailUpdate.email' => 'Format email tidak valid.',
            'emailUpdate.unique' => 'Email sudah digunakan.',
            'phoneUpdate.max' => 'Nomor telepon maksimal 15 karakter.',
            'addressUpdate.max' => 'Alamat maksimal 100 karakter.',
            'cityUpdate.max' => 'Kota maksimal 50 karakter.',
            'stateUpdate.max' => 'Provinsi maksimal 50 karakter.',
            'zipUpdate.max' => 'Kode pos maksimal 10 karakter.',
            'countryUpdate.max' => 'Negara maksimal 20 karakter.',
        ]);
        

        $supplier = ModelsSupplier::find($this->supplierId);
        $supplier->update([
            'name' => $this->nameUpdate,
            'email' => $this->emailUpdate,
            'phone' => $this->phoneUpdate,
            'address' => $this->addressUpdate,
            'city' => $this->cityUpdate,
            'state' => $this->stateUpdate,
            'zip' => $this->zipUpdate,
            'country' => $this->countryUpdate,
        ]);

        // Refresh cache setelah data diperbarui
        $this->refreshCache();

        $this->dispatch('supplierUpdated');
        $this->dispatch('updatedSuccess');
    }

    public function deleteConfirmation($id)
    {
        $supplier = ModelsSupplier::findOrFail($id);

        $this->supplierId = $supplier->id;
        $this->dispatch('showDeleteConfirmation');
    }

    public function delete()
    {
        $supplier = ModelsSupplier::findOrFail($this->supplierId);
        $supplier->delete();

        // Hapus cache terkait produk yang dihapus
        $this->removeCache();

        $this->dispatch('supplierUpdated');
        $this->dispatch('deletedSuccess');
    }

    // Fungsi untuk memperbarui cache supplier
    protected function refreshCache()
    {
        Cache::put('totalSuppliers', ModelsSupplier::count(), $this->ttl);

        $keys = Cache::get($this->cacheKeysKey, []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Cache::forget($this->cacheKeysKey);
    }

    
    // Fungsi untuk menghapus cache supplier yang dihapus
    protected function removeCache()
    {
        // Hapus semua key cache supplier
        $keys = Cache::get($this->cacheKeysKey, []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Cache::forget('totalSuppliers');
        Cache::forget($this->cacheKeysKey);
    }

    public function render()
    {
        return view('livewire.dashboard.supplier');
    }
}

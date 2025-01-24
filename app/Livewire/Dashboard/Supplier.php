<?php

namespace App\Livewire\Dashboard;

use App\Models\Supplier as ModelsSupplier;
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

    protected $listeners = [
        'supplierUpdated' => 'loadInitialSuppliers',
        'deleteConfirmed' => 'delete',
    ];

    public function mount()
    {

        $this->totalSuppliers = ModelsSupplier::count(); 
        $this->suppliers = collect();
    }

    public function updatingSearch()
    {
        $this->limit = 8;
    }

    public function updatedSearch()
    {
        usleep(500000);
        $this->loadInitialSuppliers();
    }

    public function loadInitialSuppliers()
    {
        $this->loaded = true;
        $this->suppliers = ModelsSupplier::where('name', 'like', '%'.$this->search.'%')
            ->latest()
            ->take($this->limit)
            ->get();
    }

    public function loadMore()
    {
        $this->limit =+ 8;
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

        $this->dispatch('supplierUpdated');
        $this->dispatch('deletedSuccess');
    }

    public function render()
    {
        return view('livewire.dashboard.supplier');
    }
}

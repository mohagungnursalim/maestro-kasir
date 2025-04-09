<?php

namespace App\Livewire\Dashboard;

use App\Models\Unit;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Attributes\On;


class UnitManagement extends Component
{
   
    // Store
    public $name;

    // Update
    public $unitId, $nameUpdate;

    // List Unit
    public $units, $totalUnits;

    // Search
    #[Url]
    public $search = '';
    public $limit = 8;
    public $loaded = false;


    protected $listeners = [
        'unitUpdated' => 'loadInitialUnits',
        'deleteConfirmed' => 'delete',
    ];

    public function mount()
    {
        $this->totalUnits = Unit::count();
        $this->units = collect();
    }

    public function updatingSearch()
    {
        $this->limit = 8;
    }

    public function updatedSearch()
    {
        $this->loadInitialUnits();
    }

    public function loadInitialUnits()
    {
        $this->loaded = true;
    
        $this->units = Unit::where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->take($this->limit)
            ->get();
    }
    
    public function loadMore()
    {
        $this->limit += 8;
        $this->loadInitialUnits();
    }

    #[On('resetForm')]
    public function resetForm()
    {
        $this->reset(['name']);
    }


    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:50',
        ],[
            'name.required' => 'Kolom :attribute tidak boleh kosong.',
            'name.string' => 'Kolom :attribute harus berupa teks.',
            'name.max' => 'Kolom :attribute tidak boleh lebih dari :max karakter.',
        ]);

        Unit::create([
            'name' => $this->name
        ]);

        $this->dispatch('unitUpdated');
        $this->dispatch('addedSuccess');
    }

    public function editModal($id)
    {
        $unit = Unit::findOrFail($id);
        $this->unitId = $unit->id;
        $this->nameUpdate = $unit->name;

        $this->dispatch('showEditModal');
    }

    public function update()
    {
        $this->validate([
            'nameUpdate' => 'required|string|max:50',
        ],[
            'nameUpdate.required' => 'Kolom :attribute tidak boleh kosong.',
            'nameUpdate.string' => 'Kolom :attribute harus berupa teks.',
            'nameUpdate.max' => 'Kolom :attribute tidak boleh lebih dari :max karakter.',
        ]);

        $unit = Unit::findOrFail($this->unitId);
        $unit->update([
            'name' => $this->nameUpdate
        ]);


        $this->dispatch('unitUpdated');
        $this->dispatch('updatedSuccess');
    }

    public function deleteConfirmation($id)
    {
        $unit = Unit::findOrFail($id);

        $this->unitId = $unit->id;
        $this->dispatch('showDeleteConfirmation');
    }

    public function delete()
    {
        $unit = Unit::findOrFail($this->unitId);
        $unit->delete();

        $this->dispatch('unitUpdated');
        $this->dispatch('deletedSuccess');
    }

    public function render()
    {
        return view('livewire.dashboard.unit-management', [
            'units' => $this->units,
        ]);
    }
}

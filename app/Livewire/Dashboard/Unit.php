<?php
namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Unit extends Component
{
    public $units = [];
    public $newUnit = '';
    public $selectedUnit = '';
    public $defaultUnit = ''; // Untuk menyimpan unit default saat edit
    public $mode = 'create'; // Bisa 'create' atau 'edit'

    protected $listeners = [
        'refreshUnits' => 'loadUnits',
        'setUnit' => 'setSelectedUnit'
    ];
    

    public function mount($defaultUnit = null, $mode = 'create')
    {
        $this->mode = $mode;
        $this->loadUnits();
    
        if ($this->mode === 'edit' && $defaultUnit) {
            $this->selectedUnit = $defaultUnit;
        }
    }

    public function loadUnits()
    {
        $this->units = DB::table('satuans')->orderBy('name')->pluck('name')->toArray();
    }

    public function addUnit()
    {
        $this->validate([
            'newUnit' => 'required|string|max:20|unique:satuans,name'
        ]);

        DB::table('satuans')->insert([
            'name' => $this->newUnit,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->newUnit = '';
        $this->loadUnits();
        
        $this->dispatch('unitAdded');
        session()->flash('unit-message', 'Satuan berhasil ditambahkan!');
    }

    public function setSelectedUnit($unit)
    {
        if ($this->mode === 'edit') {
            $this->selectedUnit = $unit;
        }
    }

    public function updatedSelectedUnit($value)
    {
        if ($this->mode === 'edit') {
            $this->dispatch('setUnit', $value);
        } else {
            $this->dispatch('unitSelected', $value);
        }
    }

    public function render()
    {
        return view('livewire.dashboard.unit');
    }
}

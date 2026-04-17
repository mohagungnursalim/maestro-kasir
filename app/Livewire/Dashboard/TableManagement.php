<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Table;
use Illuminate\Support\Str;
use Livewire\WithPagination;

class TableManagement extends Component
{
    use WithPagination;

    public $name = '';
    public $editId = null;

    protected $rules = [
        'name' => 'required|string|max:50',
    ];

    public function render()
    {
        $tables = Table::orderBy('created_at', 'desc')->paginate(10);
        return view('livewire.dashboard.table-management', compact('tables'));
    }

    public function resetForm()
    {
        $this->name = '';
        $this->editId = null;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        if ($this->editId) {
            $table = Table::findOrFail($this->editId);
            $table->name = $this->name;
            $table->save();
            session()->flash('success', 'Meja berhasil diperbarui.');
        } else {
            // Create new table with secure token
            Table::create([
                'name' => $this->name,
                'token' => Str::random(12),
                'is_active' => true,
            ]);
            session()->flash('success', 'Meja baru berhasil ditambahkan.');
        }

        $this->resetForm();
        $this->dispatch('close-modal');
    }

    public function edit($id)
    {
        $this->resetForm();
        $table = Table::findOrFail($id);
        $this->editId = $table->id;
        $this->name = $table->name;
        $this->dispatch('open-modal');
    }

    public function toggleStatus($id)
    {
        $table = Table::findOrFail($id);
        $table->is_active = !$table->is_active;
        $table->save();
    }

    public function delete($id)
    {
        Table::findOrFail($id)->delete();
        session()->flash('success', 'Meja berhasil dihapus.');
    }
}

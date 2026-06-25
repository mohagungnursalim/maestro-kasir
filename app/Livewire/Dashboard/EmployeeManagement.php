<?php

namespace App\Livewire\Dashboard;

use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class EmployeeManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $name;
    public $position;
    public $base_salary;
    public $deduction_per_day;
    public $joined_at;
    public $employeeId;
    public $isEdit = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'position' => 'nullable|string|max:255',
        'base_salary' => 'required|numeric|min:0',
        'deduction_per_day' => 'required|numeric|min:0',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function store()
    {
        $this->validate();

        Employee::create([
            'name' => $this->name,
            'position' => $this->position,
            'base_salary' => $this->base_salary,
            'deduction_per_day' => $this->deduction_per_day,
            'joined_at' => now(),
            'is_active' => true,
        ]);

        $this->dispatch('addedSuccess');
        $this->resetFields();
    }

    public function editModal($id)
    {
        $employee = Employee::findOrFail($id);
        $this->employeeId = $employee->id;
        $this->name = $employee->name;
        $this->position = $employee->position;
        $this->base_salary = $employee->base_salary;
        $this->deduction_per_day = $employee->deduction_per_day;
        
        $this->isEdit = true;
        $this->dispatch('showEditModal');
    }

    public function update()
    {
        $this->validate();

        $employee = Employee::findOrFail($this->employeeId);
        $employee->update([
            'name' => $this->name,
            'position' => $this->position,
            'base_salary' => $this->base_salary,
            'deduction_per_day' => $this->deduction_per_day,
        ]);

        $this->dispatch('updatedSuccess');
        $this->resetFields();
    }

    public function deleteConfirmation($id)
    {
        $this->employeeId = $id;
        $this->dispatch('showDeleteConfirmation');
    }

    #[On('deleteConfirmed')]
    public function delete()
    {
        Employee::findOrFail($this->employeeId)->delete();
        $this->dispatch('deletedSuccess');
    }

    #[On('resetForm')]
    public function resetFields()
    {
        $this->name = '';
        $this->position = '';
        $this->base_salary = '';
        $this->deduction_per_day = '';
        $this->employeeId = null;
        $this->isEdit = false;
    }

    public function render()
    {
        $employees = Employee::query()
            ->where('name', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.dashboard.employee-management', [
            'employees' => $employees
        ])->layout('layouts.app');
    }
}

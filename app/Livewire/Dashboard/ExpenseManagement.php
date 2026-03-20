<?php

namespace App\Livewire\Dashboard;

use App\Models\Expense;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class ExpenseManagement extends Component
{
    // Store & Update inputs
    public $expense_date;
    public $type = 'out';
    public $category = 'Operasional';
    public $description;
    public $amount;

    public $expenseId;
    public $isEdit = false;

    // List
    public $expenses, $totalExpenses, $totalNominalOut, $totalNominalIn;

    // Search & Filter
    #[Url]
    public $search = '';
    public $filterMonth = ''; // Format 'YYYY-MM'
    public $limit = 10;
    public $loaded = false;

    protected $listeners = [
        'expenseUpdated' => 'loadInitialExpenses',
        'deleteConfirmed' => 'delete',
        'resetForm' => 'resetForm',
    ];

    public function mount()
    {
        $this->filterMonth = date('Y-m');
        $this->expense_date = date('Y-m-d');
        $this->expenses = collect();
        $this->loadInitialExpenses();
    }

    public function updatingSearch()
    {
        $this->limit = 10;
    }

    public function updatedSearch()
    {
        $this->loadInitialExpenses();
    }

    public function updatedFilterMonth()
    {
        $this->loadInitialExpenses();
    }

    public function loadInitialExpenses()
    {
        $this->loaded = true;
        
        $query = Expense::with('user')
            ->where(function ($query) {
                $query->where('description', 'like', '%' . $this->search . '%')
                      ->orWhere('category', 'like', '%' . $this->search . '%');
            });
            
        if (!empty($this->filterMonth)) {
            $year = substr($this->filterMonth, 0, 4);
            $month = substr($this->filterMonth, 5, 2);
            $query->whereYear('expense_date', $year)->whereMonth('expense_date', $month);
        }

        if (!Auth::user()->hasRole('admin|owner')) {
             $query->where('user_id', Auth::id());
        }

        $this->totalExpenses = $query->count();
        $this->totalNominalOut = (clone $query)->where('type', 'out')->sum('amount');
        $this->totalNominalIn = (clone $query)->where('type', 'in')->sum('amount');

        $this->expenses = $query->latest('expense_date')->latest('id')
            ->take($this->limit)
            ->get();
    }
    
    public function loadMore()
    {
        $this->limit += 10;
        $this->loadInitialExpenses();
    }

    public function rules() 
    {
        return [
            'expense_date' => 'required|date',
            'type' => 'required|in:in,out',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
        ];
    }

    public function storeExpense($data)
    {
        $validated = \Illuminate\Support\Facades\Validator::make($data, $this->rules())->validate();

        Expense::create([
            'user_id' => Auth::id(),
            'expense_date' => $validated['expense_date'],
            'type' => $validated['type'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
        ]);

        $this->dispatch('expenseUpdated');
        $this->dispatch('addedSuccess');
        $this->dispatch('refreshDashboard');
    }

    public function updateExpense($id, $data)
    {
        $validated = \Illuminate\Support\Facades\Validator::make($data, $this->rules())->validate();

        $expense = Expense::findOrFail($id);
        $expense->update([
            'expense_date' => $validated['expense_date'],
            'type' => $validated['type'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
        ]);

        $this->dispatch('expenseUpdated');
        $this->dispatch('updatedSuccess');
        $this->dispatch('refreshDashboard');
    }

    public function deleteConfirmation($id)
    {
        $this->expenseId = $id;
        $this->dispatch('showDeleteConfirmation');
    }

    public function delete()
    {
        $expense = Expense::findOrFail($this->expenseId);
        $expense->delete();

        $this->dispatch('expenseUpdated');
        $this->dispatch('deletedSuccess');
        $this->dispatch('refreshDashboard');
    }

    public function render()
    {
        return view('livewire.dashboard.expense-management');
    }
}

<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;
use Illuminate\Support\Facades\Session;

class BranchSwitcher extends Component
{
    public $activeBranchId;
    public $branches;

    public function mount()
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        // Jika user ditugaskan ke cabang tertentu, batasi hanya cabang itu
        if ($user && $user->branch_id) {
            $this->branches = Branch::where('id', $user->branch_id)->get();
        } else {
            // Jika tidak ada (misal Owner), bisa lihat semua cabang
            $this->branches = Branch::all();
        }

        $this->activeBranchId = Session::get('active_branch_id');
        
        // Jika active_branch tidak ada, atau active_branch saat ini tidak diizinkan untuk user ini
        if (!$this->activeBranchId || !$this->branches->contains('id', $this->activeBranchId)) {
            $this->activeBranchId = $this->branches->first()->id ?? null;
            Session::put('active_branch_id', $this->activeBranchId);
        }
    }

    public function switchBranch($branchId)
    {
        $this->activeBranchId = $branchId;
        Session::put('active_branch_id', $branchId);
        
        // Redirect to refresh the page and apply the new branch scope globally
        return redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.branch-switcher');
    }
}

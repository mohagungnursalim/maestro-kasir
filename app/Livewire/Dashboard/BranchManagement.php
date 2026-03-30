<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class BranchManagement extends Component
{
    use WithFileUploads;

    public $branches;
    public $branchId, $name, $address, $phone, $is_active = true;
    public $isEditMode = false;

    // Store Settings Properties
    public $store_logo, $new_logo;
    public $store_footer = 'Terima Kasih';
    public $is_tax = false, $tax = 0, $is_supplier = false;

    public function mount()
    {
        $user = Auth::user();
        
        // Hanya owner, ATAU admin yang memiliki akses global (branch_id = null)
        if (!$user->hasRole('owner')) {
            if (!$user->hasRole('admin') || $user->branch_id !== null) {
                abort(403, 'Akses ditolak. Anda tidak difokuskan sebagai Admin Pusat/Global.');
            }
        }
        
        $this->loadBranches();
    }

    public function loadBranches()
    {
        $this->branches = Branch::with(['setting' => function($query) {
            $query->withoutGlobalScope('branch');
        }])->get();
    }

    protected $rules = [
        'name' => 'required|string|max:255',
        'address' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:50',
        'is_active' => 'boolean',
        'store_footer' => 'nullable|string|max:100',
        'new_logo' => 'nullable|image|mimes:svg,png,jpg,gif|max:5120',
        'is_tax' => 'nullable|boolean',
        'tax' => 'nullable|numeric|min:0|max:100',
        'is_supplier' => 'nullable|boolean'
    ];

    protected $messages = [
        'name.required' => 'Nama cabang wajib diisi.',
        'new_logo.max' => 'Ukuran gambar tidak boleh lebih dari 5MB.',
        'tax.max' => 'Pajak maksimal 100%.',
    ];

    public function resetInput()
    {
        $this->name = '';
        $this->address = '';
        $this->phone = '';
        $this->is_active = true;
        $this->isEditMode = false;
        $this->branchId = null;

        // Reset Settings
        $this->store_footer = 'Terima Kasih';
        $this->is_tax = false;
        $this->tax = 0;
        $this->is_supplier = false;
        $this->store_logo = null;
        $this->new_logo = null;
    }

    public function store()
    {
        $this->validate();

        $branch = Branch::create([
            'name' => $this->name,
            'address' => $this->address,
             'phone' => $this->phone,
            'is_active' => $this->is_active,
        ]);

        $settings = StoreSetting::create([
            'branch_id' => $branch->id,
            'store_name' => $this->name,
            'store_address' => $this->address ?? 'Alamat',
            'store_phone' => $this->phone ?? '-',
            'store_footer' => $this->store_footer ?? 'Terima Kasih',
            'is_tax' => $this->is_tax ?? false,
            'tax' => $this->tax ?? 0,
            'is_supplier' => $this->is_supplier ?? false,
        ]);

        if ($this->new_logo) {
            $imagePath = $this->new_logo->store('logos', 'public');
            $settings->update(['store_logo' => "storage/{$imagePath}"]);
        }

        $this->loadBranches();
        $this->resetInput();
        $this->dispatch('branchStored');
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        $this->branchId = $branch->id;
        $this->name = $branch->name;
        $this->address = $branch->address;
        $this->phone = $branch->phone;
        $this->is_active = (bool) $branch->is_active;
        $this->isEditMode = true;

        $settings = StoreSetting::withoutGlobalScope('branch')->where('branch_id', $this->branchId)->first();
        if ($settings) {
            $this->store_footer = $settings->store_footer;
            $this->is_tax = (bool) $settings->is_tax;
            $this->tax = $settings->tax;
            $this->is_supplier = (bool) $settings->is_supplier;
            $this->store_logo = $settings->store_logo;
        } else {
            $this->store_footer = 'Terima Kasih';
            $this->is_tax = false;
            $this->tax = 0;
            $this->is_supplier = false;
            $this->store_logo = null;
        }
        $this->new_logo = null;
    }

    public function update()
    {
        $this->validate();

        $branch = Branch::findOrFail($this->branchId);
        $branch->update([
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
        ]);

        $settings = StoreSetting::withoutGlobalScope('branch')->firstOrNew(['branch_id' => $branch->id]);

        if ($this->new_logo) {
            $imagePath = $this->new_logo->store('logos', 'public');
            if (!empty($settings->store_logo)) {
                $oldLogoPath = str_replace('storage/', '', $settings->store_logo);
                if (Storage::disk('public')->exists($oldLogoPath)) {
                    Storage::disk('public')->delete($oldLogoPath);
                }
            }
            $settings->store_logo = "storage/{$imagePath}";
        }

        $settings->store_name = $this->name;
        $settings->store_address = $this->address ?? '-';
        $settings->store_phone = $this->phone ?? '-';
        $settings->store_footer = $this->store_footer ?? 'Terima Kasih';
        $settings->is_tax = $this->is_tax ?? false;
        $settings->tax = $this->tax ?? 0;
        $settings->is_supplier = $this->is_supplier ?? false;
        $settings->save();

        $this->loadBranches();
        $this->resetInput();
        $this->dispatch('branchUpdated');
    }

    protected $listeners = [
        'deleteConfirmed' => 'delete'
    ];

    public function deleteConfirmation($id)
    {
        $this->branchId = $id;
        $this->dispatch('showDeleteConfirmation');
    }

    public function delete()
    {
        $branch = Branch::findOrFail($this->branchId);

        // Optional: protect default branch if needed
        if (Branch::count() <= 1) {
            $this->dispatch('showErrorCannotDeleteLastBranch');
            return;
        }

        $branch->delete();
        $this->loadBranches();
        $this->dispatch('branchDeleted');
    }

    public function render()
    {
        return view('livewire.dashboard.branch-management');
    }
}

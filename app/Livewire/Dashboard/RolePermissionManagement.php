<?php
namespace App\Livewire\Dashboard;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionManagement extends Component
{
    public $name, $role_id, $permissions = []; // Untuk menyimpan izin yang dipilih
    public $allPermissions, $permission_id,$roles; // Untuk menyimpan semua izin
    public $isEditMode = false; // Untuk menandakan mode edit

    public $permissionName; // Untuk tambah permission

    public function mount()
    {
        $this->roles = Role::with('permissions')->get(); // Eager load permissions
        $this->allPermissions = Permission::all();
    }
    

    // CRUD Role
    public function store()
    {
       // Daftar role yang diperbolehkan
        $allowedRoles = ['admin', 'owner', 'kasir'];

        $validatedData = $this->validate([
            'name' => [
                'required',
                'unique:roles,name',
                function ($attribute, $value, $fail) use ($allowedRoles) {
                    if (!in_array($value, $allowedRoles)) {
                        $fail('Role yang diperbolehkan hanya: admin, owner, dan kasir.');
                    }
                }
            ],
            'permissions' => 'array'
        ], [
            'name.required' => 'Nama peran wajib diisi.',
            'name.unique' => 'Nama peran sudah ada!',
            'permissions.array' => 'Format izin tidak valid.'
        ]);

        $role = Role::create(['name' => $this->name]);
        $role->syncPermissions($this->permissions);

        $this->resetInput();
        $this->mount();
    }

    public function edit($id)
    {
        $role = $this->roles->where('id', $id)->first(); // Ambil dari collection yang sudah ada
        $this->role_id = $role->id;
        $this->name = $role->name;
        $this->permissions = $role->permissions->pluck('name')->toArray();

        $this->isEditMode = true;
    }


    public function update()
    {
        $this->validate([
            'name' => 'required|unique:roles,name,' . $this->role_id,
            'permissions' => 'array'
        ], [
            'name.required' => 'Nama peran wajib diisi.',
            'name.unique' => 'Nama peran sudah ada!',
            'permissions.array' => 'Format izin tidak valid.'
        ]);

        $role = Role::find($this->role_id);
        $role->update(['name' => $this->name]);
        $role->syncPermissions($this->permissions);

        $this->resetInput();
        $this->mount();
        $this->isEditMode = false;
    }

    protected $listeners = [
        'deleteRoleConfirmed' => 'delete',
        'deletePermissionConfirmed' => 'deletePermission'
    ];

    // Modal Konfirmasi Delete Role
    public function deleteRoleConfirmation($id)
    {
        $this->role_id = $id;
        $this->dispatch('showDeleteRoleConfirmation');
    }

    public function delete()
    {
        Role::destroy($this->role_id);
        $this->mount();
        $this->dispatch('roleDeleted');
    }

    public function resetInput()
    {
        $this->name = '';
        $this->permissions = [];
    }

    public function render()
    {
        return view('livewire.dashboard.role-permission-management');
    }
}

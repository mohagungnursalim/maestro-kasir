<?php
namespace App\Livewire\Dashboard;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionManagement extends Component
{
    public $name, $role_id, $permissions = [];
    public $allPermissions, $permission_id,$roles;
    public $isEditMode = false;

    public $permissionName; // Untuk tambah permission

    public function mount()
    {
        $this->roles = Role::with('permissions')->get(); // Eager load permissions
        $this->allPermissions = Permission::all();
    }
    

    // CRUD Role
    public function store()
    {
        $validatedData = $this->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array'
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

    // CRUD Permission
    public function addPermission()
    {
        $this->validate([
            'permissionName' => 'required|unique:permissions,name'
        ]);

        Permission::create(['name' => $this->permissionName]);
        $this->permissionName = '';
        $this->mount(); // Refresh data
    }

    // Modal Konfirmasi Delete Permission
    public function deletePermissionConfirmation($id)
    {
        $this->permission_id = $id;
        $this->dispatch('showDeletePermissionConfirmation');
    }

    public function deletePermission()
    {
        Permission::destroy($this->permission_id);
        $this->mount();
        $this->dispatch('permissionDeleted'); 
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

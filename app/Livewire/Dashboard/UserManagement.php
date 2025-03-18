<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserManagement extends Component
{
    public $name, $email, $password, $user_id, $role;

    public $roles, $users;
    public $isEditMode = false;

    public function mount()
    {
        $this->roles = Role::all();
        $this->users = User::with('roles')->get();
    }

    public function store()
    {
        $validatedData = $this->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|min:6',
            'role' => 'required',
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->email),
        ]);

        $user->assignRole($this->role);

        $this->resetInput();
        $this->mount();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->user_id = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->roles->first()->name ?? null;

        $this->isEditMode = true;
    }

    public function update()
    {
        $validatedData = $this->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $this->user_id,
            'password' => 'nullable|min:6',
            'role' => 'required',
        ]);

        $user = User::findOrFail($this->user_id);

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password ? Hash::make($this->email) : $user->password,
        ]);

        $user->syncRoles($this->role);

        $this->resetInput();
        $this->mount();
        $this->isEditMode = false;
    }

    protected $listeners = [
        'deleteConfirmed' => 'delete'
    ];
    
    public function deleteConfirmation($id)
    {
        $this->user_id = $id;
        $this->dispatch('showDeleteConfirmation');
    }
    
    public function delete()
    {
        if ($this->user_id) {
            User::where('id', $this->user_id)->delete(); // Gunakan where() biar lebih aman
            $this->mount(); // Refresh daftar user
            $this->dispatch('userDeleted'); // Emit event sukses
        }
    }
    
    

    public function resetInput()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = '';
    }

    public function render()
    {
        return view('livewire.dashboard.user-management');
    }
}

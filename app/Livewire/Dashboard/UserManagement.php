<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserManagement extends Component
{
    public $name, $email, $password, $user_id, $role; // Untuk menyimpan data user
    public $roles, $users; // Untuk menyimpan semua role
    public $isEditMode = false; // Untuk menandakan mode edit

    public function mount()
    {
        $currentUser = Auth::user();

        if ($currentUser->hasRole('owner')) {
            $this->roles = Role::whereIn('name', ['admin', 'kasir'])->get();
        } elseif ($currentUser->hasRole('admin')) {
            $this->roles = Role::where('name', 'kasir')->get();
        } else {
            $this->roles = collect(); 
        }

        $this->users = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'kasir']);
        })->with('roles')->get();
    }


    // CRUD User
    public function store()
    {
        $validatedData = $this->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|min:6',
            'role' => 'required',
        ],[
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar!',
            'email.email' => 'Format email harus sesuai misal: nama@gmail.com',
            'password.min' => 'Pendek minimal password 6 karakter',
            'role.required' => 'Peran wajib diisi.'
        ]);

        // Jika password diisi, hash password baru, kalau kosong gunakan email sebagai password default
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->email),
        ]);

        $user->assignRole($this->role);

        $this->resetInput();
        $this->mount();

        $this->dispatch('userStored');
    }

    public function edit($id)
    {
        $currentUser = Auth::user();
        $user = User::findOrFail($id);

        // Cegah siapa pun mengedit akunnya sendiri (harus lewat halaman profil)
        if ($user->id === $currentUser->id) {
            $this->dispatch('showErrorCannotEditSelf');
            return;
        }

        // Jika target user adalah admin, hanya owner yang boleh edit
        if ($user->hasRole('admin') && !$currentUser->hasRole('owner')) {
            $this->dispatch('showErrorCannotEditAdmin');
            return;
        }

        // Lolos semua validasi, boleh edit
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
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar!',
            'email.email' => 'Format email harus sesuai misal: nama@gmail.com',
            'password.min' => 'Pendek minimal password 6 karakter',
            'role.required' => 'Peran wajib diisi.'
        ]);
    
        $user = User::findOrFail($this->user_id);
    
        // Jika password diisi, hash password baru, kalau kosong gunakan email sebagai password default
        $newPassword = $this->password ? Hash::make($this->password) : Hash::make($this->email);
    
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $newPassword,
        ]);
    
        $user->syncRoles($this->role);
    
        $this->resetInput();
        $this->mount();
        $this->isEditMode = false;

        $this->dispatch('userUpdated');
    }
    
    protected $listeners = [
        'deleteConfirmed' => 'delete'
    ];
    
    public function deleteConfirmation($id)
    {
        $currentUser = Auth::user();
        $user = User::findOrFail($id);

        if ($user->id === $currentUser->id) {
            $this->dispatch('showErrorCannotDeleteSelf');
            return;
        }

        if ($user->hasRole('admin') && !$currentUser->hasRole('owner')) {
            $this->dispatch('showErrorCannotDeleteAdmin');
            return;
        }

        $this->user_id = $id;
        $this->dispatch('showDeleteConfirmation');
    }

    
    
    public function delete()
    {
        $currentUser = Auth::user();
        $user = User::findOrFail($this->user_id);

        // Cegah siapa pun menghapus akun sendiri
        if ($user->id === $currentUser->id) {
            $this->dispatch('showErrorCannotDeleteSelf');
            return;
        }

        // Jika target user adalah admin, hanya owner yang boleh hapus
        if ($user->hasRole('admin') && !$currentUser->hasRole('owner')) {
            $this->dispatch('showErrorCannotDeleteAdmin');
            return;
        }

        $user->delete();
        $this->mount(); 
        $this->dispatch('userDeleted');
    }

    
    // Reset input form
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

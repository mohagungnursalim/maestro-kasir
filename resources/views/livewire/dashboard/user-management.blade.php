<div class="max-w-4xl mx-auto p-6 bg-white shadow-md rounded-lg">
    <h1 class="text-2xl font-semibold mb-4 text-gray-700">Manajemen User</h1>

    <!-- Form Input User -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

        <!-- Input Nama -->
        <div class="flex flex-col">
            <input wire:model="name" type="text" placeholder="Nama User" class="block w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500 focus:outline-none focus:ring focus:ring-opacity-40">
            @error('name') 
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
            @enderror
        </div>

        <!-- Input Email -->
        <div class="flex flex-col">
            <input wire:model="email" type="email" placeholder="Email User" class="block w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500 focus:outline-none focus:ring focus:ring-opacity-40">
            @error('email') 
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
            @enderror
        </div>

        <!-- Input Password -->
        <div class="flex flex-col">
            <input wire:model="password" type="password" placeholder="Password (Opsional)" class="block w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500 focus:outline-none focus:ring focus:ring-opacity-40">
            @error('password') 
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
            @enderror
        </div>

        <!-- Select Role -->
        <div class="flex flex-col">
            <select wire:model="role" class="block w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500 focus:outline-none focus:ring focus:ring-opacity-40">
                <option value="">- Pilih Role -</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>
            @error('role') 
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
            @enderror
        </div>

        <!-- Tombol Action -->
        <div class="col-span-2 text-center mt-4">
            @if($isEditMode)
                <button wire:click="update" class="text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2">
                    Simpan
                </button>
            @else
                <button wire:click="store" class="text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2">
                    Tambah
                </button>
            @endif
        </div>

    </div>

    <!-- Tabel User -->
    <table class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-white uppercase bg-gray-500">
            <tr>
                <th class="px-6 py-3">Nama</th>
                <th class="px-6 py-3">Email</th>
                <th class="px-6 py-3">Peran</th>
                <th class="px-6 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($users as $user)
                <tr>
                    <td class="px-6 py-4">{{ $user->name }}</td>
                    <td class="px-6 py-4">{{ $user->email }}</td>
                    <td class="px-6 py-4">{{ $user->getRoleNames()->join(', ') }}</td>
                    <td class="px-6 py-4">
                        <button wire:click="edit({{ $user->id }})" class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-sm">Ubah</button>
                        <button wire:click="deleteConfirmation({{ $user->id }})" class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-sm">Hapus</button>

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <script>
        document.addEventListener('livewire:navigated', () => {
            Livewire.on('showDeleteConfirmation', () => {
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: `Apakah Anda yakin ingin menghapus user ini?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('deleteConfirmed');
                    }
                });
            });
        });
    </script>
    
    <script>
        document.addEventListener('livewire:navigated', () => {
            Livewire.on('userDeleted', () => {  
                
                Swal.fire({
                    title: "Berhasil Dihapus!",
                    timer: 3500,
                    timerProgressBar: true,
                    icon: data.error ? "error" : "success",
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: "OK"
                });
            });
        });
    </script>      
        
</div>

<div class="p-6">

    <h1 class="text-2xl font-bold mb-6 text-gray-800">Peran & Izin User</h1>

    <!-- Input Role -->
    <div class="mb-4">
        <input wire:model="name" type="text" placeholder="Masukan nama peran.." class="block w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500 focus:outline-none focus:ring focus:ring-opacity-40">
        @error('name') 
            <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
        @enderror
    </div>

    <!-- Pilih Permission -->
    <h2 class="text-lg font-semibold mb-2 text-gray-700">Izin:</h2>
    <div class="grid grid-cols-10 gap-2 mb-4">
        @foreach($allPermissions as $permission)
            <label class="flex items-center">
                <input type="checkbox" wire:model="permissions" value="{{ $permission->name }}" class="mr-2 text-blue-500 border-gray-300 rounded focus:ring-blue-500">
                <span class="text-gray-700">{{ $permission->name }}</span>
            </label>
        @endforeach
        @error('permissions') 
            <span class="text-red-500 text-sm mt-1 col-span-10">{{ $message }}</span>
        @enderror
    </div>

    <!-- Button Tambah / Update Role -->
    <button class="text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2" wire:click="{{ $isEditMode ? 'update' : 'store' }}">
        {{ $isEditMode ? 'Simpan' : 'Tambah' }}
    </button>

    <!-- Daftar Role -->
    <table class="mt-4 w-full mb-8 text-sm text-left text-gray-500">
        <thead class="text-xs text-white uppercase bg-gray-500">
            <tr>
                <th class="px-6 py-3">Peran</th>
                <th class="px-6 py-3">Izin</th>
                <th class="px-6 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200 border border-gray-300">
            @foreach($roles as $role)
                <tr>
                    <td class="px-6 py-4 border border-gray-300">{{ $role->name }}</td>
                    <td class="px-6 py-4 border border-gray-300">{{ implode(', ', $role->permissions->pluck('name')->toArray()) }}</td>
                    <td class="px-6 py-4 border border-gray-300">
                        <button wire:click="edit({{ $role->id }})" class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-sm">Ubah</button>
                        <button wire:click="deleteRoleConfirmation({{ $role->id }})" class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-sm">Hapus</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr class="my-8">

    <!-- Input Permission -->
    <h2 class="text-lg font-semibold mb-2 text-gray-700">Tambah Izin Baru:</h2>
    <div class="mb-4">
        <input wire:model="permissionName" type="text" placeholder="Masukan nama izin.." class="block w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500 focus:outline-none focus:ring focus:ring-opacity-40">
        @error('permissionName') 
            <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
        @enderror
    </div>

    <!-- Button Tambah Permission-->
    <button class="text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2" wire:click="addPermission">Tambah Izin</button>

    <!-- Daftar Permission -->
    <table class="mt-4 w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-white uppercase bg-gray-500">
            <tr>
                <th class="px-6 py-3">Izin</th>
                <th class="px-6 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200 border border-gray-300">
            @foreach($allPermissions as $permission)
                <tr>
                    <td class="px-6 py-4 border border-gray-300">{{ $permission->name }}</td>
                    <td class="px-6 py-4 justify-end border border-gray-300">
                        <button wire:click="deletePermissionConfirmation({{ $permission->id }})" class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-sm">Hapus</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>




    {{-- Delete RoleConfirmation --}}
    <script>
        document.addEventListener('livewire:navigated', () => {
            Livewire.on('showDeleteRoleConfirmation', () => {
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: `Apakah Anda yakin ingin menghapus peran ini?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('deleteRoleConfirmed');
                    }
                });
            });
        });
    </script>
    <script>
        document.addEventListener('livewire:navigated', () => {
            Livewire.on('roleDeleted', () => {  
                
                Swal.fire({
                    title: "Peran Berhasil Dihapus!",
                    timer: 3500,
                    timerProgressBar: true,
                    icon: "success",
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: "OK"
                });
            });
        });
    </script>  


    {{-- Delete RoleConfirmation --}}
    <script>
        document.addEventListener('livewire:navigated', () => {
            Livewire.on('showDeletePermissionConfirmation', () => {
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: `Apakah Anda yakin ingin menghapus izin ini?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('deletePermissionConfirmed');
                    }
                });
            });
        });
    </script>
    <script>
        document.addEventListener('livewire:navigated', () => {
            Livewire.on('permissionDeleted', () => {  
                
                Swal.fire({
                    title: "Izin Berhasil Dihapus!",
                    timer: 3500,
                    timerProgressBar: true,
                    icon: "success",
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: "OK"
                });
            });
        });
    </script>  
</div>

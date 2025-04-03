<div class="p-6">
    <div class="flex items-center p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
        role="alert">
        <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
            fill="currentColor" viewBox="0 0 20 20">
            <path
                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
        </svg>
        <span class="sr-only">Info</span>
        <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50" role="alert">
            <span class="font-medium">Info!</span> Dalam sistem ini, terdapat <b>3 peran utama</b>:
            <b>Admin</b> (akses penuh), <b>Owner</b> (kendali utama), dan <b>Kasir</b> (terbatas tanpa izin hapus).
            Setiap peran memiliki izin berikut: <b>Tambah</b>, <b>Lihat</b>, <b>Ubah</b>, <b>Hapus</b>, dan
            <b>Unduh</b>.
            Pastikan setiap pengguna memiliki peran dan izin yang sesuai agar sistem berjalan optimal! 🚀
        </div>

    </div>
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Peran & Izin User</h1>

    <!-- Input Role -->
    <div class="mb-4">
        <input id="name" name="name" wire:model="name" type="text" placeholder="Masukan nama peran.." autocomplete="name"
            class="block w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500 focus:outline-none focus:ring focus:ring-opacity-40">
        @error('name')
        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
        @enderror
    </div>

    <!-- Pilih Permission -->
    <h2 class="text-lg font-semibold mb-2 text-gray-700">Izin:</h2>
    <div class="grid grid-cols-5 gap-2 mb-4">
        @foreach($allPermissions as $permission)
        <label class="relative inline-flex items-center cursor-pointer">
            <input id="permissions{{ $loop->iteration }}" name="permissions{{ $loop->iteration }}" type="checkbox" wire:model="permissions" value="{{ $permission->name }}" class="sr-only peer">
            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 
                        peer-focus:ring-blue-300 rounded-full peer dark:bg-gray-700 
                        peer-checked:after:translate-x-full peer-checked:after:border-white 
                        after:content-[''] after:absolute after:top-0.5 after:left-[2px] 
                        after:bg-white after:border-gray-300 after:border after:rounded-full 
                        after:h-5 after:w-5 after:transition-all dark:border-gray-600 
                        peer-checked:bg-blue-600">
            </div>
            <span class="ml-3 text-sm font-medium text-gray-700">{{ $permission->name }}</span>
        </label>
        @endforeach
    </div>

    @error('permissions')
    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
    @enderror


    <!-- Button Tambah / Update Role -->
    <button
        class="text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2"
        wire:click="{{ $isEditMode ? 'update' : 'store' }}">
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
                <td class="px-6 py-4 border border-gray-300">
                    {{ implode(', ', $role->permissions->pluck('name')->toArray()) }}</td>
                <td class="px-6 py-4 border border-gray-300">
                    <button wire:click="edit({{ $role->id }})"
                        class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-sm">Ubah</button>
                    <button wire:click="deleteRoleConfirmation({{ $role->id }})"
                        class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-sm">Hapus</button>
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

</div>

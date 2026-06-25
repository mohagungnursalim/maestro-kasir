<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                
                {{-- Toolbar --}}
                <div class="relative bg-white shadow-md sm:rounded-lg">
                    <div class="flex flex-col items-center justify-between p-4 space-y-3 md:flex-row md:space-y-0 md:space-x-4">
                        <div class="w-full md:w-1/2">
                            <button type="button" id="addEmployeeButton"
                                class="text-white bg-purple-500 hover:bg-gradient-to-bl focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 transition-all">
                                Tambah Pegawai
                            </button>
                            <div class="relative w-full mt-2">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="bi bi-search text-gray-500"></i>
                                </div>
                                <input wire:model.live.debounce.500ms="search" type="text"
                                    class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="Cari Pegawai...">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Table --}}
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-white uppercase bg-gray-500">
                            <tr>
                                <th scope="col" class="px-6 py-3">Nama</th>
                                <th scope="col" class="px-6 py-3">Posisi</th>
                                <th scope="col" class="px-6 py-3">Gaji Pokok</th>
                                <th scope="col" class="px-6 py-3">Potongan Absen</th>
                                <th scope="col" class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employees as $employee)
                                <tr class="bg-white border-b hover:bg-gray-100 text-gray-900 transition-colors">
                                    <td class="px-6 py-4 font-medium whitespace-nowrap">
                                        {{ $employee->name }}
                                    </td>
                                    <td class="px-6 py-4">{{ $employee->position }}</td>
                                    <td class="px-6 py-4">Rp {{ number_format($employee->base_salary, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="text-red-600 font-medium">-Rp {{ number_format($employee->deduction_per_day, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col space-y-1">
                                            <button wire:click="editModal({{ $employee->id }})"
                                                class="bg-green-100 hover:bg-green-200 text-green-800 text-xs font-semibold px-2.5 py-1 rounded border border-green-400 inline-flex items-center justify-center transition-all">
                                                Ubah
                                            </button>
                                            <button wire:click="deleteConfirmation({{ $employee->id }})"
                                                class="bg-red-100 hover:bg-red-200 text-red-800 text-xs font-semibold px-2.5 py-1 rounded border border-red-400 inline-flex items-center justify-center transition-all">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                        Tidak ada data pegawai ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="p-4">
                    {{ $employees->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Add --}}
    <div id="addModal" tabindex="-1" data-modal-backdrop="addModal"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full"
        wire:ignore.self>
        <div class="relative p-4 w-full max-w-lg h-full md:h-auto">
            <div class="relative p-4 bg-white rounded-xl shadow-2xl sm:p-5 border border-gray-100">
                <div class="flex justify-between items-center pb-4 mb-4 rounded-t border-b sm:mb-5">
                    <h3 class="text-lg font-bold text-gray-900">Tambah Pegawai</h3>
                </div>
                <form wire:submit.prevent="store">
                    <div class="grid gap-4 mb-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Nama Lengkap</label>
                            <input wire:model="name" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5" placeholder="Contoh: Budi Santoso" required>
                            @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Posisi / Jabatan</label>
                            <input wire:model="position" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5" placeholder="Contoh: Barista">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">Gaji Pokok (Bulan)</label>
                                <input wire:model="base_salary" type="number" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5" required>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">Potongan / Hari</label>
                                <input wire:model="deduction_per_day" type="number" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5" required>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center pt-4 border-t gap-3">
                        <button type="submit" wire:loading.attr="disabled"
                            class="text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-8 py-2.5 text-center transition-all">
                            <span wire:loading.remove wire:target="store">Simpan</span>
                            <span wire:loading wire:target="store">Menyimpan...</span>
                        </button>
                        <button type="button" id="closeAddModal"
                            class="py-2.5 px-8 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 transition-all">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div id="editModal" tabindex="-1" data-modal-backdrop="editModal"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full"
        wire:ignore.self>
        <div class="relative p-4 w-full max-w-lg h-full md:h-auto">
            <div class="relative p-4 bg-white rounded-xl shadow-2xl sm:p-5 border border-gray-100">
                <div class="flex justify-between items-center pb-4 mb-4 rounded-t border-b sm:mb-5">
                    <h3 class="text-lg font-bold text-gray-900">Ubah Data Pegawai</h3>
                </div>
                <form wire:submit.prevent="update">
                    <div class="grid gap-4 mb-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Nama Lengkap</label>
                            <input wire:model="name" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5" required>
                            @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Posisi / Jabatan</label>
                            <input wire:model="position" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">Gaji Pokok (Bulan)</label>
                                <input wire:model="base_salary" type="number" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5" required>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">Potongan / Hari</label>
                                <input wire:model="deduction_per_day" type="number" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5" required>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center pt-4 border-t gap-3">
                        <button type="submit" wire:loading.attr="disabled"
                            class="text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-8 py-2.5 text-center transition-all">
                            <span wire:loading.remove wire:target="update">Perbarui</span>
                            <span wire:loading wire:target="update">Memperbarui...</span>
                        </button>
                        <button type="button" id="closeEditModal"
                            class="py-2.5 px-8 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-purple-700 transition-all">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        document.addEventListener('livewire:navigated', () => {
            const addModal = new Modal(document.getElementById('addModal'));
            const editModal = new Modal(document.getElementById('editModal'));

            // Open Add Modal
            document.getElementById('addEmployeeButton')?.addEventListener('click', () => {
                addModal.show();
            });

            // Close Modals
            document.getElementById('closeAddModal')?.addEventListener('click', () => {
                addModal.hide();
                Livewire.dispatch('resetForm');
            });
            document.getElementById('closeEditModal')?.addEventListener('click', () => {
                editModal.hide();
                Livewire.dispatch('resetForm');
            });

            // Success Events
            Livewire.on('addedSuccess', () => {
                addModal.hide();
                showToast("Pegawai berhasil ditambahkan!");
            });

            Livewire.on('showEditModal', () => {
                editModal.show();
            });

            Livewire.on('updatedSuccess', () => {
                editModal.hide();
                showToast("Data pegawai berhasil diperbarui!");
            });

            Livewire.on('deletedSuccess', () => {
                showToast("Pegawai berhasil dihapus!");
            });

            // Delete Confirmation
            Livewire.on('showDeleteConfirmation', () => {
                Swal.fire({
                    title: 'Hapus Pegawai?',
                    text: "Data absensi terkait juga akan terpengaruh.",
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

            function showToast(message) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
                Toast.fire({
                    icon: "success",
                    title: message
                });
            }
        });
    </script>
</div>

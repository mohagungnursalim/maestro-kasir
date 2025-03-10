<div class="p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-700">📂 Daftar Laporan Transaksi</h2>

    <!-- Notifikasi -->
    @if (session()->has('message'))
    <div
        class="flex items-center p-4 mb-4 text-sm text-green-800 border border-green-300 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 dark:border-green-800"
        role="alert">
        <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 1 1 1 1v4h1a1 1 0 1 1 0 2Z"/>
        </svg>
        <span class="sr-only">Info</span>
        <div>
            <span class="font-medium text-gray-500">{{ session('message') }}</span>
        </div>
    </div>
    @endif

    @if (session()->has('error'))
        <div class="p-3 mb-4 text-red-700 bg-red-200 border border-red-400 rounded">
            {{ session('error') }}
        </div>
    @endif

  <!-- Filter -->
<div class="flex justify-end mb-4">
    <input type="date" wire:model.live.debounce.300ms="searchDate"
           class="block p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500">

    <select wire:model.live.debounce.300ms="perPage" class="block p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500">
        <option value="5">5 per halaman</option>
        <option value="10">10 per halaman</option>
        <option value="20">20 per halaman</option>
    </select>

 <!-- Tombol Refresh Page -->
 <a href="{{ route('reports') }}" onclick="showLoading()"
 class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
 Refresh
 <svg id="loading-progress" role="status"
     class="hidden inline w-4 h-4 me-3 text-gray-800 animate-spin" viewBox="0 0 100 101"
     fill="none" xmlns="http://www.w3.org/2000/svg">
     <path
     d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
     fill="#E5E7EB" />
     <path
     d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
     fill="currentColor" />
 </svg>
</a>
</div>



    <!-- Table -->
    <div class="overflow-hidden rounded-lg border border-gray-200 shadow-sm">
        <table class="w-full text-left bg-white">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-6 py-3 font-semibold">Nama File</th>
                    <th class="px-6 py-3 font-semibold text-center">Ukuran</th>
                    <th class="px-6 py-3 font-semibold text-center">Digenerate</th>
                    <th class="px-6 py-3 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($reports as $report)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 flex items-center space-x-3 text-gray-700">
                            @if ($report['extension'] === 'xlsx' || $report['extension'] === 'xls')
                                <i class="bi bi-file-earmark-excel text-green-500 text-xl"></i>
                            @elseif ($report['extension'] === 'pdf')
                                <i class="bi bi-filetype-pdf text-red-500 text-xl"></i>
                            @else
                                <i class="bi bi-file-earmark text-gray-400 text-xl"></i>
                            @endif
                            <span class="truncate max-w-[250px]">{{ $report['name'] }} </span> 
                        </td>
                        <td class="px-6 py-4 text-center text-sm">{{ $report['size'] }}</td>
                        <td class="px-6 py-4 text-center text-sm">{{ $report['last_download'] }}</td>
                        <td class="px-6 py-4 flex justify-center">
                            <a href="{{ $report['url'] }}" 
                               class="bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs font-semibold me-2 px-2.5 py-0.5 rounded border border-blue-400 inline-flex items-center justify-center">
                                ⬇️ Unduh
                            </a>
                            <button type="button" wire:click="deleteConfirmation('{{ $report['name'] }}')"
                                class="bg-red-100 hover:bg-red-200 text-red-800 text-xs font-semibold me-2 px-2.5 py-0.5 rounded border border-red-400 inline-flex items-center justify-center">
                                Hapus
                            </button>


                    
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">🚫 Tidak ada laporan tersedia.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

   <!-- Paginasi -->
    <div class="mt-4 flex items-center justify-center space-x-2">
        <button wire:click="goToPage({{ max(1, $reports->currentPage() - 1) }})" 
            class="px-4 py-2 bg-gray-300 rounded {{ $reports->onFirstPage() ? 'opacity-50 cursor-not-allowed' : '' }}"
            {{ $reports->onFirstPage() ? 'disabled' : '' }}>
            ⬅️ Prev
        </button>

        <span class="mx-3">Halaman {{ $reports->currentPage() }} dari {{ $reports->lastPage() }}</span>

        <button wire:click="goToPage({{ min($reports->lastPage(), $reports->currentPage() + 1) }})" 
            class="px-4 py-2 bg-gray-300 rounded {{ $reports->currentPage() === $reports->lastPage() ? 'opacity-50 cursor-not-allowed' : '' }}"
            {{ $reports->currentPage() === $reports->lastPage() ? 'disabled' : '' }}>
            Next ➡️
        </button>
    </div>


    {{-- Loading Progress Tombol Refresh --}}
<script>
    function showLoading() {
        document.getElementById('loading-progress').classList.remove('hidden');
    }
</script>

<script>
    document.addEventListener('livewire:navigated', () => {
        Livewire.on('showDeleteConfirmation', (filename) => {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: `Apakah Anda yakin ingin menghapus file "${filename}"?`,
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
        Livewire.on('fileDeleted', (eventData) => {  
            // Pastikan mengambil objek pertama dari array
            const data = Array.isArray(eventData) ? eventData[0] : eventData;

            Swal.fire({
                title: data.error ? "⚠️ Gagal Menghapus!" : "Berhasil Dihapus!",
                html: `<p style="font-size: 18px; line-height: 1.5;">${data.message}`,
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

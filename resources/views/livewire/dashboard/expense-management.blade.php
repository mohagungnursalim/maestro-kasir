<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                {{-- Dashboard Stats --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
                    <div class="p-4 bg-red-50 shadow rounded-lg border border-red-200">
                        <h3 class="text-sm font-semibold text-red-700">Total Kas Keluar (Pengeluaran)</h3>
                        <p class="text-2xl font-bold text-red-600">Rp{{ number_format($totalNominalOut ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-4 bg-green-50 shadow rounded-lg border border-green-200">
                        <h3 class="text-sm font-semibold text-green-700">Total Kas Masuk (Top-Up)</h3>
                        <p class="text-2xl font-bold text-green-600">Rp{{ number_format($totalNominalIn ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>

                {{-- Button Add & Search --}}
                <div class="relative bg-white shadow-md sm:rounded-lg border-t border-gray-100">
                    <div
                        class="flex flex-col items-center justify-between p-4 space-y-3 md:flex-row md:space-y-0 md:space-x-4">
                        <div class="w-full md:w-3/4 flex flex-col md:flex-row gap-3">

                            <!-- Tombol Buka Modal -->
                            <button type="button" @click="$dispatch('open-add-expense')"
                                class="text-white bg-indigo-500 hover:bg-indigo-600 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center whitespace-nowrap">
                                <i class="fas fa-plus mr-1"></i> Catat Kas
                            </button>

                            <form class="flex items-center w-full">
                                <label for="search" class="sr-only">Search</label>
                                <div class="relative w-full">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewbox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <input wire:model.live.debounce.500ms="search" type="text" id="search"
                                        class="block w-full p-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500"
                                        placeholder="Cari deskripsi atau kategori...">
                                </div>
                            </form>

                            <input type="month" wire:model.live="filterMonth" class="block w-full md:w-auto p-2.5 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500">
                        </div>
                        <div
                            class="flex flex-col items-stretch justify-end flex-shrink-0 w-full space-y-2 md:w-auto md:flex-row md:space-y-0 md:items-center md:space-x-3">
                            <a wire:loading wire:target='search, filterMonth' class="text-secondary text-sm">
                                <i class="fas fa-spinner fa-spin"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div wire:init='loadInitialExpenses' class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                        <thead class="text-xs text-white uppercase bg-gray-600">
                            <tr>
                                <th scope="col" class="px-6 py-3">Tgl</th>
                                <th scope="col" class="px-6 py-3">Jenis</th>
                                <th scope="col" class="px-6 py-3">Kategori</th>
                                <th scope="col" class="px-6 py-3">Keterangan</th>
                                <th scope="col" class="px-6 py-3">Nominal</th>
                                <th scope="col" class="px-6 py-3">User</th>
                                <th scope="col" class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (!$loaded)
                                @for ($i = 0; $i < 5; $i++) 
                                <tr class="bg-white border-b">
                                    <td colspan="7" class="px-6 py-4">
                                        <div class="h-4 bg-gray-200 rounded animate-pulse"></div>
                                    </td>
                                </tr>
                                @endfor
                            @else
                                @forelse ($expenses as $expense)
                                <tr class="bg-white border-b hover:bg-gray-100 text-gray-900">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($expense->type === 'out')
                                            <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-0.5 rounded border border-red-200">Keluar</span>
                                        @else
                                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-0.5 rounded border border-green-200">Masuk</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2 py-0.5 rounded border border-gray-300">{{ $expense->category }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $expense->description }}
                                    </td>
                                    <td class="px-6 py-4 font-bold {{ $expense->type === 'out' ? 'text-red-500' : 'text-green-500' }}">
                                        {{ $expense->type === 'out' ? '-' : '+' }}Rp{{ number_format($expense->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-400">
                                        {{ $expense->user->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 flex gap-2">
                                        <button @click="$dispatch('open-edit-expense', { expense: {{ json_encode($expense) }} })"
                                            class="bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs font-semibold px-2 md:px-2.5 py-1 rounded inline-flex items-center justify-center">
                                            <i class="fas fa-edit"></i></button>
                                        <button wire:click="deleteConfirmation({{ $expense->id }})"
                                            class="bg-red-100 hover:bg-red-200 text-red-800 text-xs font-semibold px-2 md:px-2.5 py-1 rounded inline-flex items-center justify-center">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center align-middle h-20 text-gray-500 italic">
                                        Tidak ada catatan kas ditemukan di bulan ini!
                                    </td>
                                </tr>
                                @endforelse
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Tombol Load More --}}
                @if($loaded && $expenses->count() >= $limit && $totalExpenses > $limit)
                <div class="mt-4 mb-4 flex justify-center">
                    <button wire:click="loadMore"
                        class="bg-indigo-600 text-white px-6 py-2 rounded-full shadow hover:bg-indigo-500 focus:outline-none"
                        wire:loading.remove wire:target="loadMore">
                        Tampilkan Lebih Lengkap
                    </button>

                    <button class="bg-indigo-600 text-white px-6 py-2 rounded-full shadow cursor-not-allowed" type="button" disabled wire:loading wire:target="loadMore">
                        Memuat <i class="fas fa-spinner fa-spin ms-2"></i>
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Create & Edit (Powered by AlpineJS for Zero-Network-Delay UI) --}}
    <div id="expenseModal" tabindex="-1" aria-hidden="true" 
        x-data="{
            isEdit: false,
            expenseId: null,
            expense_date: '{{ date('Y-m-d') }}',
            type: 'out',
            category: 'Bahan Baku',
            amount: '',
            description: '',
            errors: {},
            
            init() {
                this.$watch('type', value => {
                    if (value === 'in') {
                        this.category = 'Top Up';
                    } else if (value === 'out' && this.category === 'Top Up') {
                        this.category = 'Bahan Baku';
                    }
                });

                window.addEventListener('open-add-expense', () => {
                    this.isEdit = false;
                    this.expenseId = null;
                    this.expense_date = '{{ date('Y-m-d') }}';
                    this.type = 'out';
                    this.category = 'Bahan Baku';
                    this.amount = '';
                    this.description = '';
                    this.errors = {};
                    const modal = new Modal(document.getElementById('expenseModal'));
                    modal.show();
                });

                window.addEventListener('open-edit-expense', (event) => {
                    const data = event.detail.expense;
                    this.isEdit = true;
                    this.expenseId = data.id;
                    this.expense_date = data.expense_date;
                    this.type = data.type;
                    this.category = data.category;
                    this.amount = data.amount;
                    this.description = data.description;
                    this.errors = {};
                    const modal = new Modal(document.getElementById('expenseModal'));
                    modal.show();
                });
            },

            async save() {
                this.errors = {};
                // Validasi manual ringan di FE agar cepat bereaksi
                if(!this.expense_date) this.errors.expense_date = 'Tanggal harus diisi';
                if(!this.amount || this.amount <= 0) this.errors.amount = 'Nominal tidak valid';
                if(!this.description) this.errors.description = 'Deskripsi harus diisi';
                
                if(Object.keys(this.errors).length > 0) return;

                const data = {
                    expense_date: this.expense_date,
                    type: this.type,
                    category: this.category,
                    amount: this.amount,
                    description: this.description
                };

                if (this.isEdit) {
                    await $wire.updateExpense(this.expenseId, data);
                } else {
                    await $wire.storeExpense(data);
                }
            }
        }"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full"
        wire:ignore.self>
        <div class="relative p-4 w-full max-w-lg h-full md:h-auto">
            <div class="relative p-4 bg-white rounded-lg shadow border border-gray-100 sm:p-5">
                <div class="flex justify-between items-center pb-4 mb-4 rounded-t border-b sm:mb-5">
                    <h3 class="text-lg font-bold text-gray-900" x-text="isEdit ? 'Edit Catatan Kas' : 'Catat Arus Kas'"></h3>
                    <button type="button" @click="const m = new Modal(document.getElementById('expenseModal')); m.hide();" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center">
                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                
                <form @submit.prevent="save">
                    <div class="grid gap-4 mb-4 sm:grid-cols-2">
                        
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block mb-2 text-sm font-semibold text-gray-900">Tanggal</label>
                            <input x-model="expense_date" type="date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5">
                            <span x-show="errors.expense_date" x-text="errors.expense_date" class="text-red-500 text-xs"></span>
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <label class="block mb-2 text-sm font-semibold text-gray-900">Jenis Transaksi</label>
                            <select x-model="type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                <option value="out">Pengeluaran (Kas Keluar)</option>
                                <option value="in">Top-Up (Kas Masuk)</option>
                            </select>
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <label class="block mb-2 text-sm font-semibold text-gray-900">Kategori</label>
                            <select x-model="category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                <template x-if="type === 'in'">
                                    <optgroup label="Pemasukan">
                                        <option value="Top Up">Top Up (Kas Masuk)</option>
                                    </optgroup>
                                </template>
                                <template x-if="type === 'out'">
                                    <optgroup label="Pengeluaran">
                                        <option value="Bahan Baku">Bahan Baku (Sayur, Daging, dll)</option>
                                        <option value="Operasional">Operasional (Listrik, Air)</option>
                                        <option value="Gaji">Gaji / Lembur</option>
                                        <option value="Lainnya">Lain-lain</option>
                                    </optgroup>
                                </template>
                            </select>
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <label class="block mb-2 text-sm font-semibold text-gray-900">Nominal (Rp)</label>
                            <input x-model="amount" type="number" min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Contoh: 50000">
                            <span x-show="errors.amount" x-text="errors.amount" class="text-red-500 text-xs"></span>
                        </div>

                        <div class="col-span-2">
                            <label class="block mb-2 text-sm font-semibold text-gray-900">Keterangan / Deskripsi Lengkap</label>
                            <textarea x-model="description" rows="3" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500" placeholder="Misal: Beli sayur sawi 2kg di pasar..."></textarea>
                            <span x-show="errors.description" x-text="errors.description" class="text-red-500 text-xs"></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end border-t pt-4 mt-2">
                        <button type="button" @click="const m = new Modal(document.getElementById('expenseModal')); m.hide();" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 mr-2">
                            Batal
                        </button>
                        <button type="submit" class="text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            <span wire:loading.remove wire:target="storeExpense, updateExpense">Simpan Catatan</span>
                            <span wire:loading wire:target="storeExpense, updateExpense">Loading... <i class="fas fa-spinner fa-spin"></i></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- Scripts --}}
    <script>
        document.addEventListener('livewire:navigated', () => {

            Livewire.on('addedSuccess', () => {
                const modal = new Modal(document.getElementById('expenseModal'));
                modal.hide();
                Swal.fire({ toast: true, position: "top-end", showConfirmButton: false, timer: 3000, icon: "success", title: "Catatan Kas ditambahkan!" });
            });

            Livewire.on('updatedSuccess', () => {
                const modal = new Modal(document.getElementById('expenseModal'));
                modal.hide();
                Swal.fire({ toast: true, position: "top-end", showConfirmButton: false, timer: 3000, icon: "success", title: "Catatan Kas diperbarui!" });
            });

            Livewire.on('showDeleteConfirmation', () => {
                Swal.fire({
                    title: 'Hapus Catatan?',
                    text: "Data yang dihapus akan merubah kalkulasi Laba Bersih harian.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('deleteConfirmed');
                    }
                });
            });

            Livewire.on('deletedSuccess', () => {
                Swal.fire({ toast: true, position: "top-end", showConfirmButton: false, timer: 3000, icon: "success", title: "Catatan Kas dihapus!" });
            });
        });
    </script>
</div>

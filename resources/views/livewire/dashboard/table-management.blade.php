<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Manajemen Meja & QR Self-Order</h2>
        <button x-data @click="$dispatch('open-modal')"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold flex items-center gap-2 shadow-sm">
            <i class="fas fa-plus"></i> Tambah Meja
        </button>
    </div>

    @if (session()->has('success'))
        <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3">Nama/No Meja</th>
                        <th class="px-6 py-3">Link URL Self-Order</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tables as $table)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-bold text-gray-900">{{ $table->name }}</td>
                            <td class="px-6 py-4">
                                <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-md font-mono text-[11px] select-all cursor-pointer" title="Klik 2x untuk copy">
                                    {{ url('/scan/' . $table->token) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button wire:click="toggleStatus({{ $table->id }})"
                                    class="text-xs font-bold px-3 py-1 rounded-full {{ $table->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} hover:opacity-80 transition">
                                    {{ $table->is_active ? '✅ Aktif' : '❌ Nonaktif' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="window.open('{{ url('/dashboard/qr-code') }}/{{ urlencode($table->name) }}', '_blank', 'width=500,height=700')"
                                        title="Print QR"
                                        class="p-2 bg-amber-50 text-amber-600 rounded hover:bg-amber-100 transition">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <button wire:click="edit({{ $table->id }})"
                                        class="p-2 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="delete({{ $table->id }})"
                                        wire:confirm="Yakin ingin menghapus meja ini? QR Code yang sudah dicetak tidak akan bisa digunakan lagi."
                                        class="p-2 bg-red-50 text-red-600 rounded hover:bg-red-100 transition">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                Belum ada data meja. Order kasir baru yang dibuat otomatis akan tertambahkan disini jika admin telah mengaktifkannya.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-3 border-t">
            {{ $tables->links() }}
        </div>
    </div>

    {{-- MODAL TAMBAH/EDIT MEJA --}}
    <div x-data="{ show: false }"
        x-on:open-modal.window="show = true"
        x-on:close-modal.window="show = false"
        x-show="show" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="show = false"></div>

        <div x-show="show" x-transition class="relative bg-white w-full max-w-md rounded-2xl shadow-xl p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">{{ $editId ? 'Edit Meja' : 'Pendaftaran Meja Baru' }}</h3>
            
            <form wire:submit.prevent="save">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama/No Meja</label>
                        <input type="text" wire:model.defer="name" placeholder="Contoh: Meja A1, VIP 2"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                        @error('name') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    @if(!$editId)
                        <div class="p-3 bg-blue-50 text-blue-800 rounded-lg text-xs leading-relaxed flex gap-2">
                            <i class="fas fa-info-circle mt-0.5"></i>
                            <p>Sistem akan membuatkan <strong>Token Rahasia</strong> (Secure QR) secara otomatis. Pelanggan hanya bisa melihat menu meja ini jika scan QR yang benar.</p>
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex gap-3 justify-end">
                    <button type="button" @click="show = false"
                        class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-semibold transition">
                        Batal
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold flex items-center justify-center min-w-[100px] transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="save">Simpan</span>
                        <i wire:loading wire:target="save" class="fas fa-spinner fa-spin"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

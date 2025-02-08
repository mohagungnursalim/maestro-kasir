<div> 
   
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">     
                    <form wire:submit.prevent="updateSettings" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium">Nama Toko</label>
                            <input type="text" wire:model="store_name" class="w-full border-gray-300 border rounded-md p-2" required>
                            @error('store_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        
                        @if ($store_logo)
                        <img src="{{ asset($store_logo) }}" 
                             alt="Logo" 
                             class="h-24 p-2 border-2 border-gray-400 border-dashed rounded-lg">
                        @endif
                    
                        <div class="sm:col-span-2" x-data="imageUploader()" x-init="init()">
                            <!-- Input Upload -->
                            <label class="block mb-2 text-sm font-medium text-gray-900" for="new_logo">Unggah Logo</label>
                            <input wire:model='new_logo'
                                type="file"
                                id="new_logo"
                                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                                accept=".png,.jpg,.jpeg,.svg"
                                x-on:change="previewImage($event)"
                                x-ref="fileInput"
                            >
                            <p class="mt-1 text-sm text-gray-500">SVG, PNG, JPG or GIF (Max: 5MB)</p>
                        
                             <!-- Progress Container -->
                            <div x-show="uploading" x-transition:enter="transition ease-out duration-300" class="w-full">
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700">Upload Progress</span>
                                    <span class="text-sm font-medium text-gray-700" x-text="`${progress}%`"></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div
                                        class="h-2.5 rounded-full transition-all duration-150 ease-out"
                                        :style="`width: ${progress}%`"
                                        :class="{
                                            'bg-blue-600': progress < 60,
                                            'bg-yellow-400': progress >= 60 && progress < 80,
                                            'bg-green-500': progress >= 80
                                        }"
                                    ></div>
                                </div>
                            </div>
    
                            
                            <template x-if="error">
                                <span class="text-red-500 text-xs" x-text="error"></span>
                            </template>
                        
                            <!-- Tampilkan gambar preview atau gambar lama -->
                            <template x-if="imagePreview || existingImage">
                                <div class="flex justify-center items-center mt-2">
                                    <div class="flex flex-col items-center">
                                        <!-- Preview Image -->
                                        <img 
                                            :src="imagePreview || '{{ asset('storage') }}/' + existingImage" 
                                            alt="Preview" 
                                            class="max-w-full h-64 rounded-lg object-cover border-2 border-gray-300 border-dashed">
                                        
                                        <!-- File Name -->
                                        <p class="mt-4 text-sm text-gray-500">
                                            <span class="font-semibold" x-text="fileName || (existingImage ? existingImage.split('/').pop() : '')"></span>
                                            <span x-text="fileSize"></span>
                                        </p>
                                    </div>
                                </div>
                            </template>         
    
                        </div>
                        

                        <div>
                            <label class="block text-sm font-medium">Footer Toko</label>
                            <input type="text" wire:model="store_footer" class="w-full border-gray-300 border rounded-md p-2" required>
                            @error('store_footer') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                
                        <div class="flex items-center justify-center">
                            <button wire:loading.remove wire:target='updateSettings' type="submit"
                                class="text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2">
                                Simpan
                            </button>
                            <button disabled wire:loading wire:target='updateSettings' 
                                class="disabled:opacity-50 disabled:cursor-not-allowed text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2">
                                Menyimpan..
                                <svg class="inline w-5 h-5 text-white animate-spin ml-2" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                    </circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 100 8v4a8 8 0 01-8-8z"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
        </div>

<script>
        Livewire.on('successUpdate', () => {
            Swal.fire({
                title: "Success",
                text: "Data berhasil diperbarui!",
                icon: "success",
                confirmButtonText: "Oke!",
                confirmButtonColor: "#3085d6" // Warna biru dalam format hex
            });
        });
</script>
    
{{-- Alpine FileUpload Form Update --}}
<script>
    function imageUploader() {
        return {
            imagePreview:  null, 
            fileName: '',
            fileSize: '',
            error: null,
            uploading: false,
            progress: 0,
            existingImage: null,  // Bind gambar yang sudah ada
            hasImage: false,

            

            previewImage(event) {
                const file = event.target.files[0];

                if (!file) {
                    this.error = "No file selected.";
                    return;
                }

                if (!['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/svg+xml'].includes(file.type)) {
                    this.error = "Gambar harus berupa gambar (PNG, JPG, GIF, SVG).";
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    this.error = "Gambar tidak boleh lebih dari 5MB";
                    return;
                }

                this.error = null;
                this.fileName = file.name;
                this.fileSize = `${(file.size / 1024).toFixed(2)} KB`;

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                    this.hasImage = true;
                };
                reader.readAsDataURL(file);

                // Simulate progress bar
                this.simulateProgress();
            },


            simulateProgress() {
                this.uploading = true;
                this.progress = 0;

                const interval = setInterval(() => {
                    this.progress += 10;

                    if (this.progress >= 100) {
                        clearInterval(interval);
                        this.uploading = false;
                    }
                }, 200);
            },

            resetPreview() {
                this.imagePreview = null;
                this.fileName = '';
                this.fileSize = '';
                this.error = null;
                this.uploading = false;
                this.progress = 0;
                
                // Also reset the file input
                const fileInput = document.getElementById('image');
                if (fileInput) {
                    fileInput.value = '';
                }
            },


            init() {
                // Jika ada gambar yang sudah ada (misalnya saat edit), tampilkan gambar tersebut
                if (this.existingImage) {
                    this.hasImage = true;
                }

                 // Listener untuk event addedSuccess dari Livewire
                 Livewire.on('addedSuccess', () => {
                    this.resetPreview(); // Panggil untuk clear preview
                   
                });
                // Listener untuk event updatedSuccess dari Livewire
                Livewire.on('updatedSuccess', () => {
                    this.resetPreview(); // Panggil untuk clear preview
                });
            },
        };
    }
</script>
        
</div>
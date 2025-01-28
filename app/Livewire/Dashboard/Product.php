<?php

namespace App\Livewire\Dashboard;

use App\Models\Product as ModelsProduct;
use Illuminate\Database\Events\ModelsPruned;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class Product extends Component
{
    use WithFileUploads;

    // Store
    public $name,$sku, $image, $price, $description, $stock, $unit;

    // Update
    public $productId, $nameUpdate, $skuUpdate, $currentImage, $imageUpdate, $priceUpdate, $descriptionUpdate, $stockUpdate, $unitUpdate;

    // Detail
    public $nameDetail, $skuDetail, $imageDetail, $priceDetail, $descriptionDetail, $stockDetail, $unitDetail, $created_at, $updated_at;

    // List Products
    public $products, $totalProducts;

    // Search
    #[Url()]
    public $search = '';
    public $limit = 8; 
    public $loaded = false;

    protected $listeners = [
        'productUpdated' => 'loadInitialProducts',
        'deleteConfirmed' => 'delete',
    ];
    

    public function mount() 
    {  
        $ttl = 31536000; // TTL cache selama 1 tahun (dalam detik)

        // Ambil total produk dari cache atau database
        $this->totalProducts = Cache::remember('total_products', $ttl, function () {
            return ModelsProduct::count();
        });
    
        $this->products = collect();
    }

    public function updatingSearch()
    {
        $this->limit = 8;
    }

    public function updatedSearch()
    {
        usleep(500000);
        $this->loadInitialProducts();
    }

    public function loadInitialProducts()
    {
        $ttl = 31536000; // TTL cache selama 1 tahun
        $this->loaded = true;
    
        // Ambil produk sesuai pencarian dan limit dari cache atau database
        $cacheKey = "products_{$this->search}_{$this->limit}";
        $this->products = Cache::remember($cacheKey, $ttl, function () {
            return ModelsProduct::where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('sku', 'like', '%' . $this->search . '%')
                          ->orWhere('price', 'like', '%' . $this->search . '%')
                          ->orWhere('description', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->take($this->limit)
                ->get();
        });
    }
    

    public function loadMore()
    {
        $this->limit += 8;
        $this->loadInitialProducts();
    }

    #[On('resetForm')] 
    public function resetForm()
    {
        $this->reset(['name', 'sku', 'image', 'price', 'description', 'stock', 'unit']);
    }

    #[On('resetFormEdit')] 
    public function resetFormEdit()
    {
        $this->reset(['nameUpdate','skuUpdate','imageUpdate','priceUpdate', 'descriptionUpdate', 'stockUpdate', 'unitUpdate']);
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:30',
            'sku' => 'required|string|max:30',
            'image' => 'required|image|max:5120',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'stock' => 'required|numeric|min:0',
            'unit' => 'required|string|max:10',
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'name.string' => 'Nama produk harus berupa teks.',
            'name.max' => 'Nama produk maksimal 30 karakter.',
            'sku.required' => 'SKU produk wajib diisi.',
            'sku.string' => 'SKU produk harus berupa teks.',
            'sku.max' => 'SKU produk maksimal 30 karakter.',
            'image.required' => 'Gambar produk wajib diunggah.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.max' => 'Ukuran gambar maksimal 5 MB.',
            'price.required' => 'Harga produk wajib diisi.',
            'price.numeric' => 'Harga produk harus berupa angka.',
            'price.min' => 'Harga produk tidak boleh kurang dari 0.',
            'description.required' => 'Deskripsi produk wajib diisi.',
            'description.string' => 'Deskripsi produk harus berupa teks.',
            'stock.required' => 'Stok produk wajib diisi.',
            'stock.numeric' => 'Stok produk harus berupa angka.',
            'stock.min' => 'Stok produk tidak boleh kurang dari 0.',
            'unit.required' => 'Satuan produk wajib diisi.',
            'unit.string' => 'Satuan produk harus berupa teks.',
            'unit.max' => 'Satuan produk maksimal 10 karakter.',
        ]);

        $imagePath = $this->image->store('product-image', 'public');

        ModelsProduct::create([
            'name' => $this->name,
            'sku' => $this->sku,
            'image' => $imagePath,
            'price' => $this->price,
            'description' => $this->description,
            'stock' => $this->stock,
            'unit' => $this->unit,
        ]);

        // Refresh cache setelah data ditambahkan
        $this->refreshCache();

        $this->dispatch('productUpdated'); // dispatch event untuk data ter-refresh tanpa reload page
        $this->dispatch('addedSuccess');
    }

    public function editModal($id)
    {
        $product = ModelsProduct::findOrFail($id);

        $this->productId = $product->id;
        $this->nameUpdate = $product->name;
        $this->skuUpdate = $product->sku;
        $this->priceUpdate = $product->price;
        $this->descriptionUpdate = $product->description;
        $this->stockUpdate = $product->stock;
        $this->unitUpdate = $product->unit;
        $this->currentImage = $product->image; // Set gambar lama

        $this->dispatch('showEditModal');
    }

    public function update()
    {
        $this->validate([
            'nameUpdate' => 'required|string|max:30',
            'skuUpdate' => 'required|string|max:30',
            'imageUpdate' => 'nullable|image|max:5120',
            'priceUpdate' => 'required|numeric|min:0',
            'descriptionUpdate' => 'required|string',
            'stockUpdate' => 'required|numeric|min:0',
            'unitUpdate' => 'required|string|max:10',
        ], [
            'nameUpdate.required' => 'Nama produk wajib diisi.',
            'nameUpdate.string' => 'Nama produk harus berupa teks.',
            'nameUpdate.max' => 'Nama produk maksimal 30 karakter.',
            'skuUpdate.required' => 'SKU produk wajib diisi.',
            'skuUpdate.string' => 'SKU produk harus berupa teks.',
            'skuUpdate.max' => 'SKU produk maksimal 30 karakter.',
            'imageUpdate.image' => 'File yang diunggah harus berupa gambar.',
            'imageUpdate.max' => 'Ukuran gambar maksimal 5 MB.',
            'priceUpdate.required' => 'Harga produk wajib diisi.',
            'priceUpdate.numeric' => 'Harga produk harus berupa angka.',
            'priceUpdate.min' => 'Harga produk tidak boleh kurang dari 0.',
            'descriptionUpdate.required' => 'Deskripsi produk wajib diisi.',
            'descriptionUpdate.string' => 'Deskripsi produk harus berupa teks.',
            'stockUpdate.required' => 'Stok produk wajib diisi.',
            'stockUpdate.numeric' => 'Stok produk harus berupa angka.',
            'stockUpdate.min' => 'Stok produk tidak boleh kurang dari 0.',
            'unitUpdate.required' => 'Satuan produk wajib diisi.',
            'unitUpdate.string' => 'Satuan produk harus berupa teks.',
            'unitUpdate.max' => 'Satuan produk maksimal 10 karakter.',
        ]);
        

        $product = ModelsProduct::findOrFail($this->productId);

        // Jika ada gambar baru diunggah, proses upload
        if ($this->imageUpdate) {
            $newImagePath = $this->imageUpdate->store('product-image', 'public');

            // Hapus gambar lama jika ada
            if ($this->currentImage && file_exists(public_path('storage/' . $this->currentImage))) {
                unlink(public_path('storage/' . $this->currentImage));
            }

            $product->image = $newImagePath; // Update dengan gambar baru
        } else {
            $product->image = $this->currentImage; // Tetap gunakan gambar lama
        }

        $product->update([
            'name' => $this->nameUpdate,
            'sku' => $this->skuUpdate,
            'price' => $this->priceUpdate,
            'description' => $this->descriptionUpdate,
            'stock' => $this->stockUpdate,
            'unit' => $this->unitUpdate,
        ]);
        
        // Refresh cache setelah data diperbarui
        $this->refreshCache();
        
        $this->dispatch('productUpdated'); // Dispatch event untuk refresh data
        $this->dispatch('updatedSuccess');
    }


    public function detailModal($id)
    {
        $product = ModelsProduct::findOrFail($id);
        $this->nameDetail = $product->name;
        $this->skuDetail = $product->sku;
        $this->imageDetail = $product->image;
        $this->priceDetail = $product->price;
        $this->descriptionDetail = $product->description;
        $this->stockDetail = $product->stock;
        $this->unitDetail = $product->unit;
        $this->created_at = $product->created_at;
        $this->updated_at = $product->updated_at;

        $this->dispatch('showDetailModal');
    }

    public function deleteConfirmation($id)
    {
        $product = ModelsProduct::findOrFail($id);

        $this->productId = $product->id;
        $this->dispatch('showDeleteConfirmation');
    }



    public function delete()
    {
        $product = ModelsProduct::findOrFail($this->productId);
        $imagePath = $product->image;

        // Hapus file gambar jika ada
        if (file_exists(public_path('storage/' . $imagePath))) {
            unlink(public_path('storage/' . $imagePath));
        }
    
        // Hapus data 
        $product->delete();

        // Hapus cache terkait produk yang dihapus
        $this->removeCache();
        
        $this->dispatch('productUpdated'); // dispatch event untuk data ter refresh tanpa reload page 
        $this->dispatch('deleteSuccess'); 
    }

    
    protected function refreshCache()
    {
        $ttl = 31536000; // TTL cache selama 1 tahun

        // Perbarui total produk
        Cache::put('total_products', ModelsProduct::count(), $ttl);

        // Perbarui cache produk sesuai pencarian (opsional, jika perlu di-refresh seluruhnya)
        $cacheKey = "products_{$this->search}_{$this->limit}";
        Cache::put($cacheKey, ModelsProduct::where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%')
                      ->orWhere('price', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->take($this->limit)
            ->get(), $ttl);
    }

    protected function removeCache()
    {
        // Key cache produk yang relevan
        $cacheKey = "products_{$this->search}_{$this->limit}";

        // Hapus cache produk
        if (Cache::has($cacheKey)) {
            Cache::forget($cacheKey);
        }

        // Hapus cache total produk
        if (Cache::has('total_products')) {
            Cache::forget('total_products');
        }

    }



    public function render()
    {
        return view('livewire.dashboard.product');
    }
}

<?php

namespace App\Livewire\Dashboard;

use App\Models\Product as ModelsProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class Product extends Component
{
    use WithFileUploads;

    // Store Product
    public $name, $sku, $image, $price, $description, $stock, $unit, $supplier_id;

    // Update Product
    public $productId, $nameUpdate, $skuUpdate, $currentImage, $imageUpdate, $priceUpdate, $descriptionUpdate, $stockUpdate, $unitUpdate, $supplier_idUpdate, $supplierName;

    // Detail
    public $nameDetail, $skuDetail, $imageDetail, $priceDetail, $descriptionDetail, $stockDetail, $unitDetail, $created_at, $updated_at;

    // Select Product
    public $selectedProduct;

    // List Products
    public $products, $totalProducts;

    public $ttl = 31536000; // Cache selama 1 tahun
    
    

    // Search Product
    #[Url()]
    public $search = '';
    public $limit = 4; 
    public $loaded = false;

    protected $listeners = [
        'productUpdated' => 'loadInitialProducts',
        'deleteConfirmed' => 'delete',
        'unitSelected',
        'setUnit' => 'setSelectedUnit'
    ];

    public function mount() 
    {  
        // Ambil total produk dari cache atau database
        $this->totalProducts = Cache::remember('totalProducts', $this->ttl, function () {
            return DB::table('products')->count();
        });
    
        $this->products = collect(); // Inisialisasi produk sebagai koleksi kosong
    }
   
    public function unitSelected($unit)
    {
        $this->unit = $unit;
    }

    public function setSelectedUnit($unit)
    {
        $this->unitUpdate = $unit;
    }
    
    public function updatingSearch()
    {
        $this->limit = 8;
    }
    
    public function updatedSearch()
    {
        $this->loadInitialProducts();
    }
    
    public function loadInitialProducts()
    {
        $this->loaded = true;

        $key = $this->getProductsCacheKey();

        if (!Cache::has($key)) {
            $products = $this->getProductsQuery()
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('products.name', 'like', '%' . $this->search . '%')
                            ->orWhere('products.sku', 'like', '%' . $this->search . '%')
                            ->orWhere('products.price', 'like', '%' . $this->search . '%')
                            ->orWhere('products.description', 'like', '%' . $this->search . '%')
                            ->orWhere('suppliers.name', 'like', '%' . $this->search . '%');
                    });
                })
                ->orderByDesc('products.sold_count')
                ->take($this->limit)
                ->get();

            $this->cacheProducts($products);
        }

        $this->products = Cache::get($key);
    }

    public function loadMore()
    {
        // Ambil jumlah data yang sudah ada untuk dijadikan offset
        $offset = count($this->products);
    
        // Ambil data baru dengan skip offset agar tidak mengulang data lama
        $newProducts = DB::table('products')
            ->leftJoin('suppliers', 'products.supplier_id', '=', 'suppliers.id')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.price',
                'products.description',
                'products.stock',
                'products.unit',
                'products.image',
                'products.created_at',
                'products.updated_at',
                'suppliers.id as supplier_id',
                'suppliers.name as supplier_name'
            )
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('products.name', 'like', '%' . $this->search . '%')
                      ->orWhere('products.sku', 'like', '%' . $this->search . '%')
                      ->orWhere('products.price', 'like', '%' . $this->search . '%')
                      ->orWhere('products.description', 'like', '%' . $this->search . '%')
                      ->orWhere('suppliers.name', 'like', '%' . $this->search . '%'); // Pencarian nama supplier
                });
            })
            ->orderByDesc('products.sold_count')
            ->skip($offset)
            ->take($this->limit) // Ambil 4 data lagi
            ->get();
    
        // Gabungkan data baru dengan data lama
        $this->products = $this->products->merge($newProducts);
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
            'name' => 'required|string|max:60',
            'sku' => 'required|string|max:30',
            'image' => 'required|image|max:5120',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'stock' => 'required|numeric|min:0',
            'unit' => 'required|string|max:80',
            'supplier_id' => 'nullable|exists:suppliers,id',
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
            'supplier_id.exists' => 'Supplier tidak ditemukan.',

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
            'supplier_id' => $this->supplier_id ?? null,
        ]);

        $this->refreshCache();

        $this->dispatch('productUpdated');
        $this->dispatch('addedSuccess');
    }
    
    public function editModal($id)
    {
        $product = DB::table('products')
            ->leftJoin('suppliers', 'products.supplier_id', '=', 'suppliers.id')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.price',
                'products.description',
                'products.stock',
                'products.unit',
                'products.image',
                'products.supplier_id',
                'suppliers.name as supplier_name'
            )
            ->where('products.id', $id)
            ->first();

        if (!$product) {
            abort(404, 'Produk tidak ditemukan');
        }

        $this->selectedProduct = $product;
        $this->productId = $product->id;
        $this->nameUpdate = $product->name;
        $this->skuUpdate = $product->sku;
        $this->priceUpdate = $product->price;
        $this->descriptionUpdate = $product->description;
        $this->stockUpdate = $product->stock;
        $this->unitUpdate = $product->unit;
        $this->currentImage = $product->image;
        $this->supplier_idUpdate = $product->supplier_id ?? null; 
        $this->supplierName = $product->supplier_name ?? '-';

        $this->dispatch('showEditModal');
        $this->dispatch('setUnit', $this->unitUpdate); // Kirim ke Unit.php
    }


    public function update()
    {

        $this->validate([
            'nameUpdate' => 'required|string|max:60',
            'skuUpdate' => 'required|string|max:30',
            'imageUpdate' => 'nullable|image|max:5120',
            'priceUpdate' => 'required|numeric|min:0',
            'descriptionUpdate' => 'required|string',
            'stockUpdate' => 'required|numeric|min:0',
            'unitUpdate' => 'required|string|max:80',
            'supplier_idUpdate' => 'nullable|exists:suppliers,id',
        ]);

        if (!$this->selectedProduct) {
            return;
        }
    
        $data = [
            'name' => $this->nameUpdate,
            'sku' => $this->skuUpdate,
            'price' => $this->priceUpdate,
            'description' => $this->descriptionUpdate,
            'stock' => $this->stockUpdate,
            'unit' => $this->unitUpdate,
            'supplier_id' => $this->supplier_idUpdate,
        ];
    
        // Jika ada gambar baru diunggah
        if ($this->imageUpdate) {
            $newImagePath = $this->imageUpdate->store('product-image', 'public');
    
            // Hapus gambar lama jika ada
            if ($this->currentImage && Storage::exists('public/' . $this->currentImage)) {
                Storage::delete('public/' . $this->currentImage);
            }
    
            $data['image'] = $newImagePath;
        }
    
       // Update produk dengan Query Builder
        DB::table('products')->where('id', $this->productId)->update($data);
        
        $this->refreshCache(); // Perbarui cache produk

        $this->loadInitialProducts(); // Perbarui data produk

        $this->selectedProduct = collect($this->products)->firstWhere('id', $this->productId); // Perbarui data produk yang dipilih

        $this->dispatch('productUpdated');
        $this->dispatch('updatedSuccess');
    }

    
    public function detailModal($id)
    {
        $product = DB::table('products')
            ->leftJoin('suppliers', 'products.supplier_id', '=', 'suppliers.id')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.price',
                'products.description',
                'products.stock',
                'products.unit',
                'products.image',
                'products.created_at',
                'products.updated_at',
                'products.supplier_id',
                'suppliers.name as supplier_name'
            )
            ->where('products.id', $id)
            ->first();

        if (!$product) {
            abort(404, 'Produk tidak ditemukan');
        }
    
        $this->nameDetail = $product->name;
        $this->skuDetail = $product->sku;
        $this->imageDetail = $product->image;
        $this->priceDetail = $product->price;
        $this->descriptionDetail = $product->description;
        $this->stockDetail = $product->stock;
        $this->unitDetail = $product->unit;
        $this->created_at = $product->created_at;
        $this->updated_at = $product->updated_at;
        
        $this->supplier_idUpdate = $product->supplier_id ?? null; 
        $this->supplierName = $product->supplier_name ?? '-';

        $this->dispatch('showDetailModal');
    }
    
    public function deleteConfirmation($id)
    {
        // Cek apakah user ada izin Hapus
        if (!auth()->user()->can('Hapus')) {
            return redirect()->to(url()->previous());
        }

        $product = collect($this->products)->firstWhere('id', $id);
    
        if (!$product) {
            $product = DB::table('products')->where('id', $id)->first();
        }
    
        $this->selectedProduct = $product;
        $this->dispatch('showDeleteConfirmation');
    }
    
    public function delete()
    {
        // Cek apakah user ada izin Hapus
        if (!auth()->user()->can('Hapus')) {
            return redirect()->to(url()->previous());
        }
        
        if (!$this->selectedProduct) {
            return;
        }
    
        $imagePath = $this->selectedProduct->image;
    
        // Hapus gambar jika ada
        if ($imagePath && Storage::exists('public/' . $imagePath)) {
            Storage::delete('public/' . $imagePath);
        }
    
        // Hapus produk dengan Query Builder
        DB::table('products')->where('id', $this->selectedProduct->id)->delete();
    
        $this->removeCache();
        $this->dispatch('productUpdated');
        $this->dispatch('deleteSuccess');
    }

    protected function refreshCache()
    {
        Cache::put('totalProducts', DB::table('products')->count(), $this->ttl);

        $allKeys = Cache::get('product_cache_keys', []);

        foreach ($allKeys as $key) {
            Cache::forget($key);
        }

        Cache::forget('product_cache_keys');
    }

    protected function getProductsCacheKey()
    {
        return "products:{$this->search}:{$this->limit}";
    }

    protected function cacheProducts($products)
    {
        $key = $this->getProductsCacheKey();
        Cache::put($key, $products, $this->ttl);

        // Simpan key-nya supaya bisa dihapus semuanya kalau perlu
        $allKeys = Cache::get('product_cache_keys', []);
        if (!in_array($key, $allKeys)) {
            $allKeys[] = $key;
            Cache::put('product_cache_keys', $allKeys, $this->ttl);
        }
    }

    protected function removeCache()
    {
        // Hapus total produk
        Cache::forget('totalProducts');

        // Hapus semua cache produk
        $allKeys = Cache::get('product_cache_keys', []);
        foreach ($allKeys as $key) {
            Cache::forget($key);
        }
        Cache::forget('product_cache_keys');
    }

    protected function getProductsQuery()
    {
        return DB::table('products')
            ->leftJoin('suppliers', 'products.supplier_id', '=', 'suppliers.id')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.price',
                'products.description',
                'products.stock',
                'products.unit',
                'products.image',
                'products.created_at',
                'products.updated_at',
                'products.supplier_id',
                'suppliers.name as supplier_name'
            );
    }



    public function render()
    {
        return view('livewire.dashboard.product');
    }
}

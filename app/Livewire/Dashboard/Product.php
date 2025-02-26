<?php

namespace App\Livewire\Dashboard;

use App\Models\Product as ModelsProduct;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

    // Search Product
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
        $ttl = 31536000; // TTL cache selama 1 tahun
    
        // Ambil total produk dari cache atau database
        $this->totalProducts = Cache::remember('totalProducts', $ttl, function () {
            return DB::table('products')->count();
        });
    
        $this->products = collect();
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
        $ttl = 31536000; // TTL cache selama 1 tahun
        $this->loaded = true;
    
        // Cache key unik berdasarkan pencarian & limit
        $cacheKey = "products_{$this->search}_{$this->limit}";
        $this->products = Cache::remember($cacheKey, $ttl, function () {
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
                    'suppliers.name as supplier_name'
                )
                ->where(function ($query) {
                    $query->where('products.name', 'like', '%' . $this->search . '%')
                        ->orWhere('products.sku', 'like', '%' . $this->search . '%')
                        ->orWhere('products.price', 'like', '%' . $this->search . '%')
                        ->orWhere('products.description', 'like', '%' . $this->search . '%');
                })
                ->orderByDesc('products.id')
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
            'name' => 'required|string|max:60',
            'sku' => 'required|string|max:30',
            'image' => 'required|image|max:5120',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'stock' => 'required|numeric|min:0',
            'unit' => 'required|string|max:10',
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
        $product = collect($this->products)->firstWhere('id', $id);

        if (!$product) {
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
        }

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
            'unitUpdate' => 'required|string|max:10',
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
        
    
        $this->refreshCache();
        $this->dispatch('productUpdated');
        $this->dispatch('updatedSuccess');
    }
    
    public function detailModal($id)
    {
        $product = collect($this->products)->firstWhere('id', $id);
    
        if (!$product) {
            $product = DB::table('products')
                ->where('id', $id)
                ->first();
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
    
        $this->dispatch('showDetailModal');
    }
    
    public function deleteConfirmation($id)
    {
        $product = collect($this->products)->firstWhere('id', $id);
    
        if (!$product) {
            $product = DB::table('products')->where('id', $id)->first();
        }
    
        $this->selectedProduct = $product;
        $this->dispatch('showDeleteConfirmation');
    }
    
    public function delete()
    {
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
        $ttl = 31536000; 
    
        // Perbarui total produk
        $totalProducts = DB::table('products')->count();
        Cache::put('totalProducts', $totalProducts, $ttl);
    
        // Perbarui cache produk sesuai pencarian
        $cacheKey = "products_{$this->search}_{$this->limit}";
    
        $products = DB::table('products')
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
            ->where(function ($query) {
                $query->where('products.name', 'like', '%' . $this->search . '%')
                      ->orWhere('products.sku', 'like', '%' . $this->search . '%')
                      ->orWhere('products.price', 'like', '%' . $this->search . '%')
                      ->orWhere('products.description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('products.created_at', 'desc')
            ->limit($this->limit)
            ->get();
    
        Cache::put($cacheKey, $products, $ttl);
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
        if (Cache::has('totalProducts')) {
            Cache::forget('totalProducts');
        }

    }

    public function render()
    {
        return view('livewire.dashboard.product');
    }
}

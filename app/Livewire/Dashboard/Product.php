<?php

namespace App\Livewire\Dashboard;

use App\Models\Product as ModelsProduct;
use App\Models\ProductIngredient;
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
    public $is_discounted, $discount_type, $discount_value, $discount_start, $discount_end;
    public $use_stock = true; // Apakah produk ini menggunakan stok

    // HPP Store (multi-ingredient)
    public $ingredients = []; // Array of ingredient rows
    public $target_margin_percent;
    public $totalCostPerServing = 0;
    public $suggestedPrice = 0;

    // Update Product
    public $productId, $nameUpdate, $skuUpdate, $currentImage, $imageUpdate, $priceUpdate, $descriptionUpdate, $stockUpdate, $unitUpdate, $supplier_idUpdate, $supplierName;
    public $isDiscountedUpdate, $discountTypeUpdate, $discountValueUpdate, $discountStartUpdate, $discountEndUpdate;
    public $useStockUpdate = true; // Apakah produk ini menggunakan stok (update)

    // HPP Update (multi-ingredient)
    public $ingredientsUpdate = []; // Array of ingredient rows for edit
    public $targetMarginPercentUpdate;
    public $totalCostPerServingUpdate = 0;
    public $suggestedPriceUpdate = 0;

    // Detail
    public $nameDetail, $skuDetail, $imageDetail, $priceDetail, $descriptionDetail, $stockDetail, $unitDetail, $created_at, $updated_at;
    public $priceOriginalDetail;
    public $isDiscountedDetail, $discountTypeDetail, $discountValueDetail, $discountStartDetail, $discountEndDetail;
    public $useStockDetail = true;

    // HPP Detail
    public $ingredientsDetail = []; // Array of ingredients for detail view
    public $totalCostPerServingDetail = 0;
    public $targetMarginPercentDetail;
    public $suggestedPriceDetail = 0;

    // Select Product
    public $selectedProduct;

    // List Products
    public $products, $totalProducts;

    // Cache TTL
    public $ttl;
    

    // Search Product
    #[Url()]
    public $search = '';
    public $limit = 8; 
    public $loaded = false;

    protected $listeners = [
        'productUpdated' => 'loadInitialProducts',
        'deleteConfirmed' => 'delete',
        'unitSelected',
        'setUnit' => 'setSelectedUnit'
    ];

    public function mount() 
    {  
        $this->ttl = 300; // Cache selama  5 menit (format integer aman)
        $version = Cache::get('product_cache_version', 1);
        $activeBranch = \Illuminate\Support\Facades\Session::get('active_branch_id', 'all');

        // Ambil total produk dari cache atau database
        $this->totalProducts = Cache::remember("totalProducts_br{$activeBranch}_v{$version}", $this->ttl, function () {
            return DB::table('products')
                ->when(session()->has('active_branch_id'), function ($q) {
                    $q->where('branch_id', session('active_branch_id'));
                })
                ->count();
        });
            
        $this->products = collect(); // Inisialisasi produk sebagai koleksi kosong

        // Initialize with one empty ingredient row
        $this->ingredients = [$this->emptyIngredient()];
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

            $products = $this->applyDiscountToCollection($products);

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
                'products.is_discounted',
                'products.discount_type',
                'products.discount_value',
                'products.discount_start',
                'products.discount_end',
                'products.description',
                'products.stock',
                'products.use_stock',
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
            ->when(session()->has('active_branch_id'), function ($q) {
                $q->where('products.branch_id', session('active_branch_id'));
            })
            ->orderByDesc('products.sold_count')
            ->skip($offset)
            ->take($this->limit) // Ambil data lagi
            ->get();

        $newProducts = $this->applyDiscountToCollection($newProducts);
    
        // Gabungkan data baru dengan data lama
        $this->products = $this->products->merge($newProducts);
    }

    // =============================================
    // HPP INGREDIENT MANAGEMENT (MULTI-INGREDIENT)
    // =============================================

    /**
     * Return an empty ingredient row template.
     */
    protected function emptyIngredient()
    {
        return [
            'ingredient_name' => '',
            'cost_price' => '',
            'cost_quantity' => '',
            'cost_unit' => '',
            'serving_size' => '',
            'serving_unit' => '',
            'cost_per_serving' => 0,
        ];
    }

    /**
     * Add a new ingredient row to the create form.
     */
    public function addIngredient()
    {
        $this->ingredients[] = $this->emptyIngredient();
    }

    /**
     * Remove an ingredient row from the create form.
     */
    public function removeIngredient($index)
    {
        if (count($this->ingredients) > 1) {
            unset($this->ingredients[$index]);
            $this->ingredients = array_values($this->ingredients);
            $this->recalculateAllHpp();
        }
    }

    /**
     * Add a new ingredient row to the edit form.
     */
    public function addIngredientUpdate()
    {
        $this->ingredientsUpdate[] = $this->emptyIngredient();
    }

    /**
     * Remove an ingredient row from the edit form.
     */
    public function removeIngredientUpdate($index)
    {
        if (count($this->ingredientsUpdate) > 1) {
            unset($this->ingredientsUpdate[$index]);
            $this->ingredientsUpdate = array_values($this->ingredientsUpdate);
            $this->recalculateAllHppUpdate();
        }
    }

    /**
     * Recalculate HPP for all ingredients (create form).
     */
    public function recalculateAllHpp()
    {
        $totalCost = 0;

        foreach ($this->ingredients as $i => $ingredient) {
            $result = $this->calculateIngredientHpp($ingredient);
            $this->ingredients[$i]['cost_per_serving'] = $result['cost_per_serving'] ?? 0;
            $totalCost += $this->ingredients[$i]['cost_per_serving'];
        }

        $this->totalCostPerServing = round($totalCost, 2);
        $this->suggestedPrice = $this->calculateSuggestedPrice($this->totalCostPerServing, $this->target_margin_percent);
    }

    /**
     * Recalculate HPP for all ingredients (edit form).
     */
    public function recalculateAllHppUpdate()
    {
        $totalCost = 0;

        foreach ($this->ingredientsUpdate as $i => $ingredient) {
            $result = $this->calculateIngredientHpp($ingredient);
            $this->ingredientsUpdate[$i]['cost_per_serving'] = $result['cost_per_serving'] ?? 0;
            $totalCost += $this->ingredientsUpdate[$i]['cost_per_serving'];
        }

        $this->totalCostPerServingUpdate = round($totalCost, 2);
        $this->suggestedPriceUpdate = $this->calculateSuggestedPrice($this->totalCostPerServingUpdate, $this->targetMarginPercentUpdate);
    }

    // =============================================
    // RESET FORMS
    // =============================================

    #[On('resetForm')] 
    public function resetForm()
    {
        $this->reset(['name', 'sku', 'image', 'price', 'description', 'stock', 'unit', 'use_stock', 'is_discounted', 'discount_type', 'discount_value', 'discount_start', 'discount_end', 'target_margin_percent', 'totalCostPerServing', 'suggestedPrice']);
        $this->use_stock = true; // default ON
        $this->ingredients = [$this->emptyIngredient()];
    }

    #[On('resetFormEdit')] 
    public function resetFormEdit()
    {
        $this->reset(['nameUpdate','skuUpdate','imageUpdate','priceUpdate', 'descriptionUpdate', 'stockUpdate', 'unitUpdate', 'useStockUpdate', 'isDiscountedUpdate', 'discountTypeUpdate', 'discountValueUpdate', 'discountStartUpdate', 'discountEndUpdate', 'targetMarginPercentUpdate', 'totalCostPerServingUpdate', 'suggestedPriceUpdate']);
        $this->useStockUpdate = true; // default ON
        $this->ingredientsUpdate = [];
    }

    // =============================================
    // STORE
    // =============================================

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:60',
            'sku' => 'required|string|max:30',
            'image' => 'required|image|max:5120',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'stock' => 'required|numeric|min:0',
            'unit' => 'required|string|max:20',
            'use_stock' => 'boolean',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'is_discounted' => 'nullable|boolean',
            'discount_type' => 'nullable|string|in:fixed,percent',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_start' => 'nullable|date',
            'discount_end' => 'nullable|date',
            'target_margin_percent' => 'nullable|numeric|min:0|max:99.99',
            // Ingredients validation (dynamic array)
            'ingredients.*.ingredient_name' => 'nullable|string|max:60',
            'ingredients.*.cost_price' => 'nullable|numeric|min:0',
            'ingredients.*.cost_quantity' => 'nullable|numeric|min:0.001',
            'ingredients.*.cost_unit' => 'nullable|string|max:20',
            'ingredients.*.serving_size' => 'nullable|numeric|min:0.001',
            'ingredients.*.serving_unit' => 'nullable|string|max:20',
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
            'description.string' => 'Deskripsi produk harus berupa teks.',
            'stock.required' => 'Stok produk wajib diisi.',
            'stock.numeric' => 'Stok produk harus berupa angka.',
            'stock.min' => 'Stok produk tidak boleh kurang dari 0.',
            'unit.required' => 'Satuan produk wajib diisi.',
            'unit.string' => 'Satuan produk harus berupa teks.',
            'unit.max' => 'Satuan produk maksimal 20 karakter.',
            'supplier_id.exists' => 'Supplier tidak ditemukan.',
            'target_margin_percent.max' => 'Target margin maksimal 99.99%.',
        ]);

        $imagePath = $this->image->store('product-image', 'public');

        // Recalculate total HPP
        $this->recalculateAllHpp();

        // Use DB transaction to ensure atomicity
        DB::transaction(function () use ($imagePath) {
            $product = ModelsProduct::create([
                'name' => $this->name,
                'sku' => $this->sku,
                'image' => $imagePath,
                'price' => $this->price,
                'description' => $this->description,
                'stock' => $this->stock,
                'use_stock' => $this->use_stock ? true : false,
                'unit' => $this->unit,
                'supplier_id' => $this->supplier_id ?? null,
                'is_discounted' => $this->is_discounted ? true : false,
                'discount_type' => $this->discount_type ?? null,
                'discount_value' => $this->discount_value ?? null,
                'discount_start' => $this->discount_start ?? null,
                'discount_end' => $this->discount_end ?? null,
                // HPP aggregates
                'total_cost_per_serving' => $this->totalCostPerServing > 0 ? $this->totalCostPerServing : null,
                'target_margin_percent' => $this->target_margin_percent ?? null,
                'suggested_price' => $this->suggestedPrice > 0 ? $this->suggestedPrice : null,
            ]);

            // Batch insert all valid ingredients (no N+1)
            $ingredientRows = $this->buildIngredientInsertData($product->id, $this->ingredients);
            if (!empty($ingredientRows)) {
                DB::table('product_ingredients')->insert($ingredientRows);
            }
        });

        $this->refreshCache();

        $this->dispatch('productUpdated');
        $this->dispatch('addedSuccess');
        $this->dispatch('resetUnitSelection');
    }
    
    public function editModal($id)
    {
        $this->reset('imageUpdate'); // Bersihkan state gambar saat berganti produk

        $product = DB::table('products')
            ->leftJoin('suppliers', 'products.supplier_id', '=', 'suppliers.id')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.price',
                'products.is_discounted',
                'products.discount_type',
                'products.discount_value',
                'products.discount_start',
                'products.discount_end',
                'products.description',
                'products.stock',
                'products.use_stock',
                'products.unit',
                'products.image',
                'products.supplier_id',
                'suppliers.name as supplier_name',
                // HPP aggregates
                'products.total_cost_per_serving',
                'products.target_margin_percent',
                'products.suggested_price'
            )
            ->where('products.id', $id)
            ->first();

        if (!$product) {
            abort(404, 'Produk tidak ditemukan');
        }

        // Compute discount state for single product
        $now = Carbon::now();
        $isActive = false;
        try {
            if (!empty($product->is_discounted)) {
                $startOk = true;
                $endOk = true;
                if (!empty($product->discount_start)) {
                    $startOk = $now->greaterThanOrEqualTo(Carbon::parse($product->discount_start));
                }
                if (!empty($product->discount_end)) {
                    $endOk = $now->lessThanOrEqualTo(Carbon::parse($product->discount_end));
                }
                $isActive = $startOk && $endOk;
            }
        } catch (\Exception $e) {
            $isActive = false;
        }

        $finalPrice = $product->price;
        if ($isActive && !empty($product->discount_type) && !empty($product->discount_value)) {
            if ($product->discount_type === 'fixed') {
                $finalPrice = max(0, $product->price - $product->discount_value);
            } else {
                $finalPrice = max(0, $product->price - ($product->price * ($product->discount_value / 100)));
            }
        }

        $product->is_active_discount = $isActive;
        $product->final_price = $finalPrice;

        $this->selectedProduct = $product;
        $this->productId = $product->id;
        $this->nameUpdate = $product->name;
        $this->skuUpdate = $product->sku;
        $this->priceUpdate = $product->price;
        $this->useStockUpdate = isset($product->use_stock) ? (bool) $product->use_stock : true;
        // Prefer the computed active-discount state so the toggle reflects expiry
        $this->isDiscountedUpdate = !empty($product->is_active_discount)
            ? true
            : (!empty($product->is_discounted) ? (bool) $product->is_discounted : false);
        $this->discountTypeUpdate = $product->discount_type;
        $this->discountValueUpdate = $product->discount_value;
        $this->discountStartUpdate = $product->discount_start;
        $this->discountEndUpdate = $product->discount_end;
        $this->descriptionUpdate = $product->description;
        $this->stockUpdate = $product->stock;
        $this->unitUpdate = $product->unit;
        $this->currentImage = $product->image;
        $this->supplier_idUpdate = $product->supplier_id ?? null; 
        $this->supplierName = $product->supplier_name ?? '-';

        // HPP aggregates
        $this->targetMarginPercentUpdate = $product->target_margin_percent;
        $this->totalCostPerServingUpdate = $product->total_cost_per_serving ?? 0;
        $this->suggestedPriceUpdate = $product->suggested_price ?? 0;

        // Load ingredients in ONE query (no N+1)
        $this->ingredientsUpdate = DB::table('product_ingredients')
            ->where('product_id', $id)
            ->select('id', 'ingredient_name', 'cost_price', 'cost_quantity', 'cost_unit', 'serving_size', 'serving_unit', 'servings_per_purchase', 'cost_per_serving')
            ->get()
            ->map(fn($row) => (array) $row)
            ->toArray();

        // If no ingredients exist, add one empty row
        if (empty($this->ingredientsUpdate)) {
            $this->ingredientsUpdate = [$this->emptyIngredient()];
        }

        $this->dispatch('resetImagePreview'); // Beritahu Alpine.js untuk mereset input file
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
            'useStockUpdate' => 'boolean',
            'isDiscountedUpdate' => 'nullable|boolean',
            'discountTypeUpdate' => 'nullable|string|in:fixed,percent',
            'discountValueUpdate' => 'nullable|numeric|min:0',
            'discountStartUpdate' => 'nullable|date',
            'discountEndUpdate' => 'nullable|date',
            'descriptionUpdate' => 'nullable|string',
            'stockUpdate' => 'required|numeric|min:0',
            'unitUpdate' => 'required|string|max:20',
            'supplier_idUpdate' => 'nullable|exists:suppliers,id',
            'targetMarginPercentUpdate' => 'nullable|numeric|min:0|max:99.99',
            // Ingredients validation
            'ingredientsUpdate.*.ingredient_name' => 'nullable|string|max:60',
            'ingredientsUpdate.*.cost_price' => 'nullable|numeric|min:0',
            'ingredientsUpdate.*.cost_quantity' => 'nullable|numeric|min:0.001',
            'ingredientsUpdate.*.cost_unit' => 'nullable|string|max:20',
            'ingredientsUpdate.*.serving_size' => 'nullable|numeric|min:0.001',
            'ingredientsUpdate.*.serving_unit' => 'nullable|string|max:20',
        ]);

        if (!$this->selectedProduct) {
            return;
        }

        // Recalculate total HPP
        $this->recalculateAllHppUpdate();
    
        $data = [
            'name' => $this->nameUpdate,
            'sku' => $this->skuUpdate,
            'price' => $this->priceUpdate,
            'use_stock' => $this->useStockUpdate ? true : false,
            'is_discounted' => $this->isDiscountedUpdate ? true : false,
            'discount_type' => $this->discountTypeUpdate ?? null,
            'discount_value' => $this->discountValueUpdate ?? null,
            'discount_start' => $this->discountStartUpdate ?? null,
            'discount_end' => $this->discountEndUpdate ?? null,
            'description' => $this->descriptionUpdate,
            'stock' => $this->stockUpdate,
            'unit' => $this->unitUpdate,
            'supplier_id' => $this->supplier_idUpdate,
            // HPP aggregates
            'total_cost_per_serving' => $this->totalCostPerServingUpdate > 0 ? $this->totalCostPerServingUpdate : null,
            'target_margin_percent' => $this->targetMarginPercentUpdate ?? null,
            'suggested_price' => $this->suggestedPriceUpdate > 0 ? $this->suggestedPriceUpdate : null,
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

        // Use DB transaction
        DB::transaction(function () use ($data) {
            // Update produk
            DB::table('products')->where('id', $this->productId)->update($data);

            // Delete old ingredients in ONE query, then batch insert new ones (no N+1)
            DB::table('product_ingredients')->where('product_id', $this->productId)->delete();

            $ingredientRows = $this->buildIngredientInsertData($this->productId, $this->ingredientsUpdate);
            if (!empty($ingredientRows)) {
                DB::table('product_ingredients')->insert($ingredientRows);
            }
        });
        
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
                'products.is_discounted',
                'products.discount_type',
                'products.discount_value',
                'products.discount_start',
                'products.discount_end',
                'products.description',
                'products.stock',
                'products.use_stock',
                'products.unit',
                'products.image',
                'products.created_at',
                'products.updated_at',
                'products.supplier_id',
                'suppliers.name as supplier_name',
                // HPP aggregates
                'products.total_cost_per_serving',
                'products.target_margin_percent',
                'products.suggested_price'
            )
            ->where('products.id', $id)
            ->first();

        if (!$product) {
            abort(404, 'Produk tidak ditemukan');
        }
    
        $this->nameDetail = $product->name;
        $this->skuDetail = $product->sku;
        $this->imageDetail = $product->image;
        $this->priceOriginalDetail = $product->price;
        $this->priceDetail = $product->final_price ?? $product->price;
        $this->isDiscountedDetail = $product->is_active_discount ?? (!empty($product->is_discounted) ? $product->is_discounted : false);
        $this->discountTypeDetail = $product->discount_type;
        $this->discountValueDetail = $product->discount_value;
        $this->discountStartDetail = $product->discount_start;
        $this->discountEndDetail = $product->discount_end;
        $this->descriptionDetail = $product->description;
        $this->stockDetail = $product->stock;
        $this->useStockDetail = isset($product->use_stock) ? (bool) $product->use_stock : true;
        $this->unitDetail = $product->unit;
        $this->created_at = $product->created_at;
        $this->updated_at = $product->updated_at;
        
        $this->supplier_idUpdate = $product->supplier_id ?? null; 
        $this->supplierName = $product->supplier_name ?? '-';

        // HPP aggregates
        $this->totalCostPerServingDetail = $product->total_cost_per_serving ?? 0;
        $this->targetMarginPercentDetail = $product->target_margin_percent;
        $this->suggestedPriceDetail = $product->suggested_price ?? 0;

        // Load ingredients in ONE query (no N+1)
        $this->ingredientsDetail = DB::table('product_ingredients')
            ->where('product_id', $id)
            ->select('ingredient_name', 'cost_price', 'cost_quantity', 'cost_unit', 'serving_size', 'serving_unit', 'servings_per_purchase', 'cost_per_serving')
            ->get()
            ->map(fn($row) => (array) $row)
            ->toArray();

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
    
        // Hapus produk (ingredients auto-deleted via CASCADE)
        DB::table('products')->where('id', $this->selectedProduct->id)->delete();
    
        $this->removeCache();
        $this->dispatch('productUpdated');
        $this->dispatch('deleteSuccess');
    }

    protected function refreshCache()
    {
        Cache::increment('product_cache_version');
    }

    protected function getProductsCacheKey()
    {
        $version = Cache::get('product_cache_version', 1);
        $searchHash = md5($this->search);
        $activeBranch = \Illuminate\Support\Facades\Session::get('active_branch_id', 'all');
        return "products_list_br{$activeBranch}_v{$version}_{$searchHash}_{$this->limit}";
    }

    protected function cacheProducts($products)
    {
        $key = $this->getProductsCacheKey();
        Cache::put($key, $products, $this->ttl);
    }

    protected function removeCache()
    {
        Cache::increment('product_cache_version');
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
                'products.is_discounted',
                'products.discount_type',
                'products.discount_value',
                'products.discount_start',
                'products.discount_end',
                'products.description',
                'products.stock',
                'products.use_stock',
                'products.unit',
                'products.image',
                'products.created_at',
                'products.updated_at',
                'products.supplier_id',
                'suppliers.name as supplier_name'
            )
            ->when(session()->has('active_branch_id'), function ($q) {
                $q->where('products.branch_id', session('active_branch_id'));
            });
    }

    /**
     * Apply discount computation to a collection of product records.
     * Adds `is_active_discount` (bool) and `final_price` (decimal) to each item.
     */
    protected function applyDiscountToCollection($products)
    {
        $now = Carbon::now();

        return collect($products)->map(function ($p) use ($now) {
            $isActive = false;

            try {
                if (!empty($p->is_discounted)) {
                    $startOk = true;
                    $endOk = true;

                    if (!empty($p->discount_start)) {
                        $startOk = $now->greaterThanOrEqualTo(Carbon::parse($p->discount_start));
                    }
                    if (!empty($p->discount_end)) {
                        $endOk = $now->lessThanOrEqualTo(Carbon::parse($p->discount_end));
                    }

                    $isActive = $startOk && $endOk;
                }
            } catch (\Exception $e) {
                $isActive = false;
            }

            $final = $p->price;
            if ($isActive && !empty($p->discount_type) && !empty($p->discount_value)) {
                if ($p->discount_type === 'fixed') {
                    $final = max(0, $p->price - $p->discount_value);
                } else {
                    $final = max(0, $p->price - ($p->price * ($p->discount_value / 100)));
                }
            }

            $p->is_active_discount = $isActive;
            $p->final_price = $final;

            return $p;
        });
    }

    // =============================================
    // HPP CALCULATION CORE
    // =============================================

    /**
     * Calculate HPP for a single ingredient.
     */
    protected function calculateIngredientHpp(array $ingredient)
    {
        $result = ['cost_per_serving' => 0, 'servings_per_purchase' => 0];

        $costPrice = $ingredient['cost_price'] ?? null;
        $costQuantity = $ingredient['cost_quantity'] ?? null;
        $costUnit = $ingredient['cost_unit'] ?? null;
        $servingSize = $ingredient['serving_size'] ?? null;
        $servingUnit = $ingredient['serving_unit'] ?? null;

        if (empty($costPrice) || empty($costQuantity) || empty($costUnit) || empty($servingSize) || empty($servingUnit)) {
            return $result;
        }

        $conversionFactor = $this->getUnitConversionFactor($costUnit, $servingUnit);
        if ($conversionFactor === null) {
            return $result;
        }

        $totalInServingUnit = $costQuantity * $conversionFactor;
        $servingsPerPurchase = $totalInServingUnit / $servingSize;

        if ($servingsPerPurchase <= 0) {
            return $result;
        }

        return [
            'servings_per_purchase' => round($servingsPerPurchase, 2),
            'cost_per_serving' => round($costPrice / $servingsPerPurchase, 2),
        ];
    }

    /**
     * Calculate suggested price from total cost and margin.
     */
    protected function calculateSuggestedPrice($totalCost, $marginPercent)
    {
        if ($totalCost <= 0 || empty($marginPercent) || $marginPercent >= 100) {
            return 0;
        }
        return round($totalCost / (1 - ($marginPercent / 100)), 0);
    }

    /**
     * Build batch insert data for product_ingredients (no N+1).
     * Only includes valid (filled) ingredient rows.
     */
    protected function buildIngredientInsertData($productId, array $ingredients)
    {
        $now = now();
        $rows = [];

        foreach ($ingredients as $ingredient) {
            // Skip empty rows
            if (empty($ingredient['ingredient_name']) || empty($ingredient['cost_price'])) {
                continue;
            }

            $hppResult = $this->calculateIngredientHpp($ingredient);

            $rows[] = [
                'product_id' => $productId,
                'ingredient_name' => $ingredient['ingredient_name'],
                'cost_price' => $ingredient['cost_price'],
                'cost_quantity' => $ingredient['cost_quantity'],
                'cost_unit' => $ingredient['cost_unit'],
                'serving_size' => $ingredient['serving_size'],
                'serving_unit' => $ingredient['serving_unit'],
                'servings_per_purchase' => $hppResult['servings_per_purchase'] ?? null,
                'cost_per_serving' => $hppResult['cost_per_serving'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $rows;
    }

    /**
     * Get unit conversion factor from source unit to target unit.
     * Returns null if conversion is not possible.
     */
    protected function getUnitConversionFactor($from, $to)
    {
        $from = strtolower(trim($from));
        $to = strtolower(trim($to));

        if ($from === $to) {
            return 1;
        }

        // Weight conversions
        $weightConversions = [
            'kg' => ['gram' => 1000, 'g' => 1000, 'gr' => 1000, 'ons' => 10, 'mg' => 1000000],
            'gram' => ['kg' => 0.001, 'g' => 1, 'gr' => 1, 'ons' => 0.01, 'mg' => 1000],
            'g' => ['kg' => 0.001, 'gram' => 1, 'gr' => 1, 'ons' => 0.01, 'mg' => 1000],
            'gr' => ['kg' => 0.001, 'gram' => 1, 'g' => 1, 'ons' => 0.01, 'mg' => 1000],
            'ons' => ['kg' => 0.1, 'gram' => 100, 'g' => 100, 'gr' => 100, 'mg' => 100000],
            'mg' => ['kg' => 0.000001, 'gram' => 0.001, 'g' => 0.001, 'gr' => 0.001, 'ons' => 0.00001],
        ];

        // Volume conversions
        $volumeConversions = [
            'liter' => ['ml' => 1000, 'l' => 1, 'cc' => 1000, 'gelas' => 4],
            'l' => ['ml' => 1000, 'liter' => 1, 'cc' => 1000, 'gelas' => 4],
            'ml' => ['liter' => 0.001, 'l' => 0.001, 'cc' => 1, 'gelas' => 0.004],
            'cc' => ['liter' => 0.001, 'l' => 0.001, 'ml' => 1, 'gelas' => 0.004],
            'gelas' => ['liter' => 0.25, 'l' => 0.25, 'ml' => 250, 'cc' => 250],
        ];

        // Quantity conversions (pieces, packs, etc.)
        $quantityConversions = [
            'lusin' => ['pcs' => 12, 'buah' => 12, 'biji' => 12, 'butir' => 12],
            'pcs' => ['lusin' => 1/12, 'buah' => 1, 'biji' => 1, 'butir' => 1],
            'buah' => ['lusin' => 1/12, 'pcs' => 1, 'biji' => 1, 'butir' => 1],
            'biji' => ['lusin' => 1/12, 'pcs' => 1, 'buah' => 1, 'butir' => 1],
            'butir' => ['lusin' => 1/12, 'pcs' => 1, 'buah' => 1, 'biji' => 1],
        ];

        // Check weight
        if (isset($weightConversions[$from][$to])) {
            return $weightConversions[$from][$to];
        }

        // Check volume
        if (isset($volumeConversions[$from][$to])) {
            return $volumeConversions[$from][$to];
        }

        // Check quantity
        if (isset($quantityConversions[$from][$to])) {
            return $quantityConversions[$from][$to];
        }

        return null; // Incompatible units
    }

    public function render()
    {
        return view('livewire.dashboard.product');
    }
}

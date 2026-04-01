<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    use \App\Traits\BelongsToBranch;

    protected $guarded = ['id'];
    protected $table = 'products';

    // Relasi ke Supplier (1 produk hanya dimiliki oleh 1 supplier)
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Relasi ke TransactionDetail (1 produk bisa dimiliki oleh banyak detail transaksi)
    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'product_id');
    }

    // Relasi ke ProductIngredient (1 produk bisa punya banyak bahan baku)
    public function ingredients(): HasMany
    {
        return $this->hasMany(ProductIngredient::class, 'product_id');
    }
}
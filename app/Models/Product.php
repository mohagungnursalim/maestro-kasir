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

    public function getIsActiveDiscountAttribute()
    {
        if (empty($this->is_discounted)) {
            return false;
        }

        $now = \Carbon\Carbon::now();
        $startOk = empty($this->discount_start) || $now->greaterThanOrEqualTo(\Carbon\Carbon::parse($this->discount_start));
        $endOk = empty($this->discount_end) || $now->lessThanOrEqualTo(\Carbon\Carbon::parse($this->discount_end));

        return $startOk && $endOk;
    }

    public function getOriginalPriceAttribute()
    {
        return $this->attributes['price'] ?? 0;
    }

    public function getFinalPriceAttribute()
    {
        $price = $this->original_price;
        if ($this->is_active_discount && !empty($this->discount_type) && !empty($this->discount_value)) {
            if ($this->discount_type === 'fixed') {
                return max(0, $price - $this->discount_value);
            } else {
                return max(0, $price - ($price * ($this->discount_value / 100)));
            }
        }
        return $price;
    }
}
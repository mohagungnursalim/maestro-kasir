<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDetail extends Model
{
    protected $guarded = ['id'];
    protected $table = 'transaction_details';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Relasi ke Order (1 transaksi detail hanya dimiliki oleh 1 order)
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // Relasi ke Product (1 transaksi detail hanya dimiliki oleh 1 produk)
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

}

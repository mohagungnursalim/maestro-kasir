<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $guarded = ['id'];
    protected $table = 'orders';

    // Relasi ke TransactionDetail (1 order bisa punya banyak detail transaksi)
    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'order_id');
    }

    // Relasi ke User (1 order hanya dimiliki oleh 1 user)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
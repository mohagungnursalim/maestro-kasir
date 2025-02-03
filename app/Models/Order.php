<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = ['id'];
    protected $table = 'orders';

    // Relasi ke model Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
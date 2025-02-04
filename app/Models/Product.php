<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    protected $guarded = ['id'];
    protected $table = 'products';
    protected $with = ['supplier']; // Auto-load relasi supplier

    // Relasi ke model Supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Relasi ke model Order
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
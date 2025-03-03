<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $guarded = ['id'];
    protected $table = 'suppliers';

    // Relasi ke Product (1 supplier bisa memiliki banyak produk)
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

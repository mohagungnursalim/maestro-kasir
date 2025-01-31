<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $guarded = ['id'];
    protected $table = 'suppliers';

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

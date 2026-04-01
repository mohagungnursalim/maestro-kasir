<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductIngredient extends Model
{
    protected $guarded = ['id'];
    protected $table = 'product_ingredients';

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

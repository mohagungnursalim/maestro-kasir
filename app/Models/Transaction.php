<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = ['id'];
    protected $table = 'transactions';

    // Relasi ke model Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
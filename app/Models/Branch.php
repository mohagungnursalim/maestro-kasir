<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $guarded = ['id'];

    public function setting()
    {
        return $this->hasOne(StoreSetting::class);
    }
}

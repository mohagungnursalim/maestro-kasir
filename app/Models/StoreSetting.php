<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use \App\Traits\BelongsToBranch;

    protected $guarded = ['id'];
    protected $table = 'store_settings';
}

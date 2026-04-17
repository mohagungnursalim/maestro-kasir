<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use \App\Traits\BelongsToBranch;

    protected $fillable = [
        'name',
        'token',
        'branch_id',
        'is_active',
    ];
}

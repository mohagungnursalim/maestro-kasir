<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use \App\Traits\BelongsToBranch;

    protected $guarded = ['id'];
    protected $table = 'units';
}

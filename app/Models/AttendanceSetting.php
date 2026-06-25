<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToBranch;

class AttendanceSetting extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'off_days',
        'branch_id',
    ];

    protected $casts = [
        'off_days' => 'array',
    ];
}

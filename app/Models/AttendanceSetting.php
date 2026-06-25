<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'off_days',
    ];

    protected $casts = [
        'off_days' => 'array',
    ];
}

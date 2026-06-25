<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToBranch;

class Attendance extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'employee_id',
        'date',
        'status',
        'notes',
        'branch_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

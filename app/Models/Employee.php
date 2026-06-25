<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToBranch;

class Employee extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'name',
        'position',
        'base_salary',
        'deduction_per_day',
        'joined_at',
        'is_active',
        'branch_id',
    ];

    protected $casts = [
        'joined_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function calculateSalary($month, $year) 
    {
        // 1. Get base salary
        $base = $this->base_salary;
        
        // 2. Count absences where status is 'absent' or whatever means deduction
        // Usually, 'leave' might also have deduction or not (depends on policy). We will count 'absent'.
        $absences = $this->attendances()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'absent')
            ->count();
            
        $deduction = $absences * $this->deduction_per_day;
        
        $total = $base - $deduction;
        
        return [
            'base_salary' => $base,
            'absences' => $absences,
            'deduction' => $deduction,
            'total_salary' => $total,
        ];
    }
}

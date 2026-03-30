<?php

namespace App\Traits;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

trait BelongsToBranch
{
    public static function bootBelongsToBranch()
    {
        // When creating a model, automatically assign it to the active branch
        static::creating(function ($model) {
            if (Session::has('active_branch_id') && ! $model->branch_id) {
                $model->branch_id = Session::get('active_branch_id');
            }
        });

        // Add a global scope to filter only rows that belong to the active branch
        static::addGlobalScope('branch', function (Builder $builder) {
            if (Session::has('active_branch_id')) {
                $builder->where($builder->getModel()->getTable() . '.branch_id', Session::get('active_branch_id'));
            }
        });
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}

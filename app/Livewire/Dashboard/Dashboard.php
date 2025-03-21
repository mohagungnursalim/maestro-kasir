<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use App\Models\Product;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Dashboard extends Component
{
    public $totalOrders;
    public $totalSales;
    public $totalProductsSold;

    public $filterType = 'today'; // Default Hari Ini

    public function mount()
    {
        try {
            $this->updateStats();
        } catch (\Exception $e) {
            Log::error("Error in mount(): " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    public function updateStats()
    {
        try {
            $dates = $this->getDateRange($this->filterType);

            $this->totalOrders = Order::whereBetween('created_at', [$dates['start'], $dates['end']])->count();
            $this->totalSales = Order::whereBetween('created_at', [$dates['start'], $dates['end']])->sum('grandtotal');
            $this->totalProductsSold = TransactionDetail::whereBetween('created_at', [$dates['start'], $dates['end']])->sum('quantity');
        } catch (\Exception $e) {
            Log::error("Error in updateStats(): " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    public function getDateRange($type)
    {
        try {
            switch ($type) {
                case 'week':
                    return ['start' => Carbon::now()->startOfWeek(), 'end' => Carbon::now()->endOfWeek()];
                case 'month':
                    return ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()->endOfMonth()];
                case 'year':
                    return ['start' => Carbon::now()->startOfYear(), 'end' => Carbon::now()->endOfYear()];
                default: // today
                    return ['start' => Carbon::today(), 'end' => Carbon::today()->endOfDay()];
            }
        } catch (\Exception $e) {
            Log::error("Error in getDateRange(): " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ['start' => Carbon::today(), 'end' => Carbon::today()->endOfDay()]; // Fallback ke hari ini
        }
    }

    public function updatedFilterType()
    {
        try {
            $this->updateStats();
        } catch (\Exception $e) {
            Log::error("Error in updatedFilterType(): " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    public function render()
    {
        try {
            return view('livewire.dashboard.dashboard');
        } catch (\Exception $e) {
            Log::error("Error in render(): " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return view('livewire.dashboard.dashboard');
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search; // Ambil nilai pencarian
        $suppliers = Supplier::where('name', 'like', "%$search%")
            ->orderBy('name', 'asc')
            ->limit(10)
            ->get(['id', 'name']); // Hanya ambil ID & Name

        return response()->json($suppliers);

    }
}

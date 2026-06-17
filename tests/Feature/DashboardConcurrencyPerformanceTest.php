<?php

namespace Tests\Feature;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Support\Benchmark;

class DashboardConcurrencyPerformanceTest extends TestCase
{
    /**
     * Test benchmark perbandingan antara eksekusi berurutan (Sequential) dan serentak (Concurrency).
     * Dapat dijalankan menggunakan perintah:
     * php artisan test --filter DashboardConcurrencyPerformanceTest
     * 
     * Catatan: Jika database dalam keadaan kosong/sangat cepat, Concurrency mungkin
     * terlihat lebih lambat karena ada overhead/waktu yang dibutuhkan saat spawn process (membuat proses latar belakang).
     * Concurrency akan menang telak ketika jumlah data di database sudah besar atau per query memakan waktu > 50-100ms.
     */
    public function test_benchmark_sequential_vs_concurrency()
    {
        // 1. Definisikan tanggal/periode filter default
        $start = Carbon::today()->startOfDay();
        $end = Carbon::today()->endOfDay();

        // 2. Skema Sequential (Berurutan)
        $sequentialSchema = function () use ($start, $end) {
            $ordersAggregates = DB::table('orders')
                ->whereBetween('created_at', [$start, $end])
                ->where('payment_status', 'PAID')
                ->selectRaw("
                    COUNT(id) as total_orders,
                    COALESCE(SUM(grandtotal), 0) as total_sales,
                    COALESCE(SUM(CASE WHEN upper(payment_method) = 'QRIS' THEN grandtotal ELSE 0 END), 0) as total_qris,
                    COALESCE(SUM(CASE WHEN upper(payment_method) IN ('CASH', 'TUNAI') THEN grandtotal ELSE 0 END), 0) as total_cash,
                    COALESCE(AVG(grandtotal), 0) as avg_order_value,
                    COALESCE(AVG(discount), 0) as avg_discount
                ")->first();

            $salesAggregates = DB::table('transaction_details')
                ->join('orders', 'transaction_details.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->where('orders.payment_status', 'PAID')
                ->selectRaw('COALESCE(SUM(transaction_details.quantity), 0) as total_qty')
                ->first();

            $expensesAggregates = DB::table('expenses')
                ->whereBetween('expense_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN type = 'out' THEN amount ELSE 0 END), 0) as total_out,
                    COALESCE(SUM(CASE WHEN type = 'in' THEN amount ELSE 0 END), 0) as total_in
                ")->first();

            return [
                $ordersAggregates,
                $salesAggregates,
                $expensesAggregates
            ];
        };

        // 3. Skema Concurrency (Bersamaan)
        $concurrencySchema = function () use ($start, $end) {
            return Concurrency::run([
                function () use ($start, $end) {
                    return DB::table('orders')
                        ->whereBetween('created_at', [$start, $end])
                        ->where('payment_status', 'PAID')
                        ->selectRaw("
                            COUNT(id) as total_orders,
                            COALESCE(SUM(grandtotal), 0) as total_sales,
                            COALESCE(SUM(CASE WHEN upper(payment_method) = 'QRIS' THEN grandtotal ELSE 0 END), 0) as total_qris,
                            COALESCE(SUM(CASE WHEN upper(payment_method) IN ('CASH', 'TUNAI') THEN grandtotal ELSE 0 END), 0) as total_cash,
                            COALESCE(AVG(grandtotal), 0) as avg_order_value,
                            COALESCE(AVG(discount), 0) as avg_discount
                        ")->first();
                },

                function () use ($start, $end) {
                    return DB::table('transaction_details')
                        ->join('orders', 'transaction_details.order_id', '=', 'orders.id')
                        ->whereBetween('orders.created_at', [$start, $end])
                        ->where('orders.payment_status', 'PAID')
                        ->selectRaw('COALESCE(SUM(transaction_details.quantity), 0) as total_qty')
                        ->first();
                },

                function () use ($start, $end) {
                    return DB::table('expenses')
                        ->whereBetween('expense_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                        ->selectRaw("
                            COALESCE(SUM(CASE WHEN type = 'out' THEN amount ELSE 0 END), 0) as total_out,
                            COALESCE(SUM(CASE WHEN type = 'in' THEN amount ELSE 0 END), 0) as total_in
                        ")->first();
                }
            ]);
        };

        // 4. Lakukan Benchmark, ukur berapa ms 
        $results = Benchmark::measure([
            'Sequential (Tanpa Concurrency)' => $sequentialSchema,
            'Concurrent (Pakai Concurrency)' => $concurrencySchema,
        ], 3); // Lakukan 3 iterasi agar lebih stabil nilainya

        // 5. Kita cetak hasilnya di console
        dump('Hasil Benchmark Laravel (dalam ms):', $results);

        // Jika Anda ingin memastikan output schema dapat berjalan, kita bisa assert true
        $sequentialResult = $sequentialSchema();
        $concurrentResult = $concurrencySchema();

        $this->assertIsArray($sequentialResult);
        $this->assertIsArray($concurrentResult);
    }
}

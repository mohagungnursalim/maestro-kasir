<?php

if (!function_exists('decimal')) {
    /**
     * Mengonversi nilai menjadi string desimal presisi tinggi.
     *
     * @param string|float|int|null $value Nilai yang ingin dikonversi
     * @param int $precision Jumlah angka di belakang koma
     * @return string
     */
    function decimal($value, $precision = 2): string
    {
        if (is_null($value) || $value === '') {
            return number_format(0, $precision, '.', '');
        }

        // Hilangkan karakter non-numerik kecuali titik dan koma
        $clean = preg_replace('/[^0-9.,-]/', '', (string) $value);

        // Ganti koma dengan titik jika user input lokal
        $normalized = str_replace(',', '.', $clean);

        // Tambahkan nol untuk jaga presisi
        return bcadd($normalized, '0', $precision);
    }
}

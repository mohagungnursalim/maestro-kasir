import './bootstrap';

document.addEventListener('livewire:navigated', () => {
    if (typeof window.initFlowbite === 'function') window.initFlowbite();
    if (typeof window.initGoogleCharts === 'function') window.initGoogleCharts();

    // Pastikan chart di-inisialisasi setiap navigasi SPA Livewire v3
    window.initDashboardChartsOnly();

    const fsBtn = document.getElementById('fullscreenBtn');
    if (fsBtn && !window.fsBtnListenerRegistered) {
        window.fsBtnListenerRegistered = true;
        fsBtn.addEventListener('click', function () {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    alert(`Error attempting to enable full-screen mode: ${err.message}`);
                });
            } else {
                document.exitFullscreen();
            }
        });
    }

    // Pastikan hanya menginisialisasi audio sekali
    if (!window.selectSound) {
        window.selectSound = new Audio("/audio/click-sound.mp3");
        window.selectSound.preload = "auto";
    }

    // (Chart initialization is moved to initDashboardChartsOnly outside this block)

    if (!window.successSound) {
        window.successSound = new Audio("/audio/success-sound.mp3");
        window.successSound.volume = 1.0;
        window.successSound.preload = "auto";
    }

    if (!window.errorSound) {
        window.errorSound = new Audio("/audio/error-sound.mp3");
        window.errorSound.volume = 1.0;
        window.errorSound.preload = "auto";
    }

    window.playSelectSound = function () {
        window.selectSound.currentTime = 0;
        window.selectSound.play().catch(error => {
            console.error("Gagal memutar audio:", error);
        });
    };

    window.playSuccessSound = function () {
        window.successSound.currentTime = 0;
        window.successSound.play().catch(error => {
            console.error("Gagal memutar audio:", error);
        });
    };

    window.playErrorSound = function () {
        window.errorSound.currentTime = 0;
        window.errorSound.play().catch(error => {
            console.error("Gagal memutar audio:", error);
        });
    };

    // Hindari duplikasi event listener dengan mengecek apakah sudah terdaftar
    if (!window.livewireEventRegistered) {
        window.livewireEventRegistered = true;

        Livewire.on('errorOrderType', (shortage) => {
            playErrorSound();

            const formattedShortage = parseFloat(shortage).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            Swal.fire({
                title: "Oops!",
                text: `Meja/Nama wajib diisi,jika makan/minum ditempat!`,
                icon: "warning",
                confirmButtonText: "Oke!",
                confirmButtonColor: "#3085d6"
            });
        });


        Livewire.on('successSaveOrder', () => {
            playSuccessSound();
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: "Order ditambahkan ke bayar nanti!",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        });

        Livewire.on('orderUnpaid', () => {
            playSelectSound();
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: "Order ditambahkan ke Cart!",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        });

        Livewire.on('successPayment', () => {
            playSuccessSound();
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: "Pembayaran berhasil 🙌!",
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true
            });
        });

        Livewire.on('successDeleteOrder', () => {
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: "Pesanan berhasil dihapus!",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        });

        Livewire.on('errorPayment', () => {
            playErrorSound();
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "error",
                title: "Pembayaran gagal 🥲!",
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true
            });
        });

        Livewire.on('insufficientPayment', (shortage) => {
            playErrorSound();

            const formattedShortage = parseFloat(shortage).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            Swal.fire({
                title: "Pembayaran Kurang!",
                text: `Uang pelanggan kurang Rp${formattedShortage}!`,
                icon: "warning",
                confirmButtonText: "Oke!",
                confirmButtonColor: "#3085d6"
            });
        });



        Livewire.on('insufficientStock', (insufficientProducts) => {
            if (!Array.isArray(insufficientProducts) || insufficientProducts.length === 0) return;
            playErrorSound();
            Swal.fire({
                title: "Stok Tidak Cukup!",
                text: "Stok tidak cukup untuk produk berikut:\n\n" + insufficientProducts.join("\n"),
                icon: "warning",
                confirmButtonText: "Oke!",
                confirmButtonColor: "#3085d6"
            });
        });
    }


    // Buat fungsi dan assign ke window
    window.toggleTaxInput = function () {
        const isChecked = document.getElementById('isTaxCheckbox')?.checked;
        const taxInputGroup = document.getElementById('taxInputGroup');

        if (isChecked && taxInputGroup) {
            taxInputGroup.classList.remove('hidden');
        } else if (taxInputGroup) {
            taxInputGroup.classList.add('hidden');
        }
    };

    // Jalankan sekali saat halaman pertama kali dimuat
    document.addEventListener('DOMContentLoaded', () => {
        window.toggleTaxInput();
    });

    // Jalankan lagi saat Livewire nge-re-render komponen
    document.addEventListener('livewire:load', () => {
        Livewire.hook('message.processed', () => {
            window.toggleTaxInput();
        });
    });




});

// Fungsi khusus untuk menginisialisasi chart saja agar lebih aman
window.initDashboardChartsOnly = function() {
    if (typeof window.initMonthlyTurnoverChart === 'function') window.initMonthlyTurnoverChart();
    if (typeof window.renderDailyOmzetFromWire === 'function') window.renderDailyOmzetFromWire();
    if (typeof window.renderPeakHourChart === 'function') window.renderPeakHourChart();
    if (typeof window.initProductSalesChart === 'function') window.initProductSalesChart();
};

// Eksekusi untuk first load / refresh biasa agar chart tetap muncul
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.initDashboardChartsOnly);
} else {
    window.initDashboardChartsOnly();
}


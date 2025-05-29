import './bootstrap';

document.addEventListener('livewire:navigated', () => {
    window.initFlowbite();

    document.getElementById('fullscreenBtn').addEventListener('click', function () {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                alert(`Error attempting to enable full-screen mode: ${err.message}`);
            });
        } else {
            document.exitFullscreen();
        }
    });
    
    // Pastikan hanya menginisialisasi audio sekali
    if (!window.selectSound) {
        window.selectSound = new Audio("/audio/click-sound.mp3");
        window.selectSound.preload = "auto";
    }

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

        Livewire.on('successPayment', () => {
            playSuccessSound();
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: "Pembayaran berhasil 🙌!",
                showConfirmButton: false,
                timer: 10000,
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
                timer: 10000,
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

    
});

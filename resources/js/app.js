import './bootstrap';

document.addEventListener('livewire:navigated', () => {
    window.initFlowbite();

    if (!window.selectSound) {
        window.selectSound = new Audio("/audio/click-sound.mp3");
        window.selectSound.preload = "auto";
    }
    
    window.playSelectSound = function () {
        window.selectSound.currentTime = 0;
        window.selectSound.play().catch(error => {
            console.error("Gagal memutar audio:", error);
        });
    };

    document.getElementById("fullscreenBtn").addEventListener("click", function () {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    });

    Livewire.on('successPayment', (event) => {
        playSuccessSound();
       const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 10000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });
            Toast.fire({
                icon: "success",
                title: "Pembayaran berhasil 🙌!"
            });

    });

    function playSuccessSound() {
        let audio = new Audio("/audio/success-sound.mp3"); // Buat objek audio baru setiap kali diputar
        audio.volume = 1.0; // Atur volume penuh
        audio.play().catch(error => console.error("Gagal memutar audio:", error)); // Tangkap error jika ada
    }

    Livewire.on('errorPayment', (event) => {
        playErrorSound(); // Panggil fungsi pemutaran audio 
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 10000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });
            Toast.fire({
                icon: "error",
                title: "Pembayaran gagal 🥲!"
            });

    });

    Livewire.on('insufficientPayment', (shortage) => {
        playErrorSound(); // Panggil fungsi pemutaran audio

        Swal.fire({
            title: "Pembayaran Kurang!",
            text: `Uang pelanggan kurang Rp${shortage.toLocaleString('id-ID')}!`,
            icon: "warning",
            confirmButtonText: "Oke!",
            confirmButtonColor: "#3085d6"
        });
    });

    Livewire.on('insufficientStock', (insufficientProducts) => {
        if (!Array.isArray(insufficientProducts) || insufficientProducts.length === 0) return;

        playErrorSound(); // Panggil fungsi pemutaran audio

        Swal.fire({
            title: "Stok Tidak Cukup!",
            text: "Stok tidak cukup untuk produk berikut:\n\n" + insufficientProducts.join("\n"),
            icon: "warning",
            confirmButtonText: "Oke!",
            confirmButtonColor: "#3085d6"
        });
    });

    function playErrorSound() {
        let audio = new Audio("/audio/error-sound.mp3"); // Buat objek audio baru setiap kali diputar
        audio.volume = 1.0; // Atur volume penuh
        audio.play().catch(error => console.error("Gagal memutar audio:", error)); // Tangkap error jika ada
    }
});
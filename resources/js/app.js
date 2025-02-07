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
    
});
import './bootstrap';

import Alpine from 'alpinejs'

window.Alpine = Alpine

// Wait for Livewire to load first
document.addEventListener('DOMContentLoaded', function() {
    // Delay Alpine start to let Livewire initialize first
    setTimeout(() => {
        Alpine.start();
        console.log('🔥 Alpine started after Livewire');
    }, 100);
});

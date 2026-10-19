document.addEventListener('livewire:load', function() {
    document.querySelectorAll('input[autocomplete="off"]').forEach(el => {
        if (el.value && el.getAttribute('data-autofilled')) {
            el.value = '';
        }
        // Mark as not autofilled initially
        el.dataset.autofilled = 'false';
    });

    // Observe changes – if the value appears without user interaction, clear it
    document.addEventListener('focusin', function(e) {
        const input = e.target.closest('input[autocomplete="off"]');
        if (input && input.value && !input.dataset.userEdited) {
            input.value = '';
        }
    });
    document.addEventListener('input', function(e) {
        const input = e.target.closest('input[autocomplete="off"]');
        if (input) {
            input.dataset.userEdited = 'true';
        }
    });
});
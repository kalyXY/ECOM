document.addEventListener('DOMContentLoaded', function() {
    // Update cart count on page load
    if (typeof updateCartCount === 'function') {
        updateCartCount();
    }

    // Auto-save quantities
    document.querySelectorAll('input[name^="quantities"]').forEach(function(input) {
        input.addEventListener("change", function() {
            this.form.submit();
        });
    });
});

function decreaseQty(id) {
    const input = document.getElementById('qty-' + id);
    const currentValue = parseInt(input.value);
    if (currentValue > 1) {
        input.value = currentValue - 1;
        input.form.submit();
    }
}

function increaseQty(id) {
    const input = document.getElementById('qty-' + id);
    const currentValue = parseInt(input.value);
    if (currentValue < 99) {
        input.value = currentValue + 1;
        input.form.submit();
    }
}

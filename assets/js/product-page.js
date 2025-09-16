document.addEventListener('DOMContentLoaded', function() {
    // Image gallery
    const mainImage = document.querySelector('.product-main-image img');
    const thumbnails = document.querySelectorAll('.product-thumbnails img');

    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            mainImage.src = this.src;
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Wishlist button
    const wishlistBtn = document.querySelector('.wishlist-btn');
    if (wishlistBtn) {
        wishlistBtn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            toggleWishlist(productId);
        });
    }

    // Update cart count on page load
    if (typeof updateCartCount === 'function') {
        updateCartCount();
    }

    // Adapt quantity max based on size selection
    const sizeInputs = document.querySelectorAll('input[name="size"]');
    const qtyInput = document.querySelector('input[name="quantity"]');

    if (sizeInputs.length > 0 && qtyInput) {
        function updateQtyMax(stock) {
            const max = Math.max(1, Math.min(10, stock));
            qtyInput.max = max;
            if (parseInt(qtyInput.value) > max) {
                qtyInput.value = max;
            }
        }

        sizeInputs.forEach(r => {
            r.addEventListener('change', () => {
                const stock = parseInt(r.dataset.stock || '0', 10);
                updateQtyMax(stock);
            });
            if (r.checked) {
                const stock = parseInt(r.dataset.stock || '0', 10);
                updateQtyMax(stock);
            }
        });
    }
});

// JavaScript for shop page interactivity: add to cart functionality

document.addEventListener('DOMContentLoaded', function() {
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    const cartCountElement = document.getElementById('cart-count');

    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            if (!productId) return;

            fetch('cart-process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'add',
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product added to cart.');
                    updateCartCount();
                } else {
                    alert(data.message || 'Failed to add product to cart.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding product to cart.');
            });
        });
    });

    function updateCartCount() {
        fetch('cart-process.php?action=count')
            .then(response => response.json())
            .then(data => {
                if (data.success && cartCountElement) {
                    cartCountElement.textContent = data.cart_count;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    // Initial cart count update
    updateCartCount();
});

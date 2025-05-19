document.addEventListener('DOMContentLoaded', () => {
    const cartItemsContainer = document.getElementById('cart-items');
    const emptyCartMessage = document.getElementById('empty-cart-message');
    const subtotalElement = document.getElementById('subtotal');
    const shippingElement = document.getElementById('shipping');
    const taxElement = document.getElementById('tax');
    const totalElement = document.getElementById('total');
    const clearCartBtn = document.getElementById('clear-cart-btn');

    const SHIPPING_COST = 100;
    const TAX_RATE = 0.05;

    // Fetch and render cart items
    async function loadCartItems() {
        try {
            const response = await fetch('cart-process.php?action=get');
            const data = await response.json();

            if (data.success && data.items.length > 0) {
                emptyCartMessage.classList.add('hidden');
                cartItemsContainer.innerHTML = '';

                let subtotal = 0;

                data.items.forEach(item => {
                    subtotal += item.price * item.quantity;

                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'py-4 flex items-center space-x-4';

                    itemDiv.innerHTML = `
                        <img src="${item.image_url || 'IDP-2-main/images.jpeg'}" alt="${item.name}" class="w-20 h-20 object-cover rounded-md">
                        <div class="flex-1">
                            <h3 class="text-gray-800 font-medium">${item.name}</h3>
                            <p class="text-gray-600">Quantity: ${item.quantity}</p>
                            <p class="text-gray-600">Price: ৳${item.price.toFixed(2)}</p>
                        </div>
                        <div class="text-gray-800 font-semibold">৳${(item.price * item.quantity).toFixed(2)}</div>
                    `;

                    cartItemsContainer.appendChild(itemDiv);
                });

                subtotalElement.textContent = `৳${subtotal.toFixed(2)}`;
                shippingElement.textContent = `৳${SHIPPING_COST.toFixed(2)}`;
                const tax = subtotal * TAX_RATE;
                taxElement.textContent = `৳${tax.toFixed(2)}`;
                const total = subtotal + SHIPPING_COST + tax;
                totalElement.textContent = `৳${total.toFixed(2)}`;
            } else {
                cartItemsContainer.innerHTML = '';
                emptyCartMessage.classList.remove('hidden');
                subtotalElement.textContent = '৳0.00';
                shippingElement.textContent = `৳${SHIPPING_COST.toFixed(2)}`;
                taxElement.textContent = '৳0.00';
                totalElement.textContent = '৳0.00';
            }
        } catch (error) {
            console.error('Error loading cart items:', error);
        }
    }

    // Clear cart
    clearCartBtn.addEventListener('click', async () => {
        if (!confirm('Are you sure you want to clear the cart?')) return;

        try {
            const response = await fetch('cart-process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'clear'
                })
            });
            const data = await response.json();
            if (data.success) {
                loadCartItems();
                const cartCountElement = document.getElementById('cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = '0';
                }
            } else {
                alert(data.message || 'Failed to clear cart.');
            }
        } catch (error) {
            console.error('Error clearing cart:', error);
            alert('An error occurred while clearing the cart.');
        }
    });

    loadCartItems();
});

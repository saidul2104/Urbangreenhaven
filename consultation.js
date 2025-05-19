document.addEventListener('DOMContentLoaded', () => {
    const consultationForm = document.getElementById('consultation-form');
    const bookingModal = document.getElementById('booking-modal');
    const closeModalBtn = document.getElementById('close-booking-modal');
    const consultationTypeDisplay = document.getElementById('consultation-type-display');
    const consultationTypeInput = document.getElementById('consultation_type');
    const consultationPriceInput = document.getElementById('consultation_price');
    const consultationErrors = document.getElementById('consultation-errors');

    // Show modal when select buttons are clicked
    document.querySelectorAll('.select-consultation-btn').forEach(button => {
        button.addEventListener('click', () => {
            const type = button.getAttribute('data-type');
            const price = button.getAttribute('data-price');
            consultationTypeDisplay.textContent = type;
            consultationTypeInput.value = type;
            consultationPriceInput.value = price;
            consultationErrors.classList.add('hidden');
            consultationErrors.textContent = '';
            bookingModal.classList.remove('hidden');
        });
    });

    // Close modal
    closeModalBtn.addEventListener('click', () => {
        bookingModal.classList.add('hidden');
    });

    // Handle form submission via AJAX
    consultationForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        consultationErrors.classList.add('hidden');
        consultationErrors.textContent = '';

        const formData = new FormData(consultationForm);

        try {
            const response = await fetch(consultationForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                alert('Consultation request submitted successfully.');
                bookingModal.classList.add('hidden');
                consultationForm.reset();
            } else {
                consultationErrors.textContent = result.message || 'An error occurred. Please try again.';
                consultationErrors.classList.remove('hidden');
            }
        } catch (error) {
            consultationErrors.textContent = 'An error occurred. Please try again.';
            consultationErrors.classList.remove('hidden');
        }
    });
});

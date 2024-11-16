document.addEventListener('DOMContentLoaded', function () {
    const bookingForm = document.getElementById('bookingForm');
    const checkAvailabilityButton = document.querySelector('.availability-btn');

    // Check availability handler
    checkAvailabilityButton.addEventListener('click', function (event) {
        event.preventDefault();

        const formData = new FormData(bookingForm);

        // Fetch request to check date availability
        fetch('../Database/booking.php?action=check_availability', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Available',
                        text: data.message
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Not Available',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Something went wrong!'
                });
            });
    });
});

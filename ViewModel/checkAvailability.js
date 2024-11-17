document.addEventListener('DOMContentLoaded', function () {
    const bookingForm = document.getElementById('bookingForm');
    const checkAvailabilityButton = document.querySelector('.availability-btn');
    const bookNowButton = document.querySelector('.submit-btn');

    checkAvailabilityButton.addEventListener('click', function (event) {
        event.preventDefault();

        const formData = new FormData(bookingForm);

        fetch('../Model/Database/booking.php?action=check_availability', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Available',
                        text: data.message,
                        showCancelButton: true,
                        confirmButtonText: 'Book Now',
                        cancelButtonText: 'Cancel',
                    }).then(result => {
                        if (result.isConfirmed) {
                            bookNow();
                        }
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
                    text: 'Unable to check availability. Please try again later.'
                });
            });
    });

    bookNowButton.addEventListener('click', function (event) {
        event.preventDefault();
        bookNow();
    });

    function bookNow() {
        const formData = new FormData(bookingForm);
        formData.append('action', 'book_now');

        fetch('../Model/Database/booking.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Booking Confirmed',
                        text: 'Your booking has been successfully confirmed!',
                        confirmButtonText: 'View Receipt'
                    }).then(() => {
                        window.location.href = '../Database/booking.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Booking Failed',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Booking failed. Please try again later.'
                });
            });
    }
});

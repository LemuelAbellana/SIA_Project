document.addEventListener('DOMContentLoaded', function () {
    const bookingForm = document.getElementById('bookingForm');
    const checkAvailabilityButton = document.querySelector('.availability-btn');
    const bookNowButton = document.querySelector('.submit-btn');

    // Event: Check Availability
    checkAvailabilityButton.addEventListener('click', function (event) {
        event.preventDefault();

        // Validate dates
        const arrivalDate = document.getElementById('arrival_date').value.trim();
        const leavingDate = document.getElementById('leaving_date').value.trim();

        if (!validateDates(arrivalDate, leavingDate)) return;

        // Prepare form data
        const formData = new FormData(bookingForm);
        formData.append('action', 'check_availability');

        // Check availability
        sendRequest(formData, '../Database/booking.php')
            .then((data) => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Available',
                        text: data.message,
                        showCancelButton: true,
                        confirmButtonText: 'Book Now',
                        cancelButtonText: 'Cancel',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            bookNow(formData);
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Not Available',
                        text: data.message,
                    });
                }
            })
            .catch((error) => handleError(error));
    });

    // Event: Book Now
    bookNowButton.addEventListener('click', function (event) {
        event.preventDefault();

        const formData = new FormData(bookingForm);
        formData.append('action', 'book_now');

        bookNow(formData);
    });

    // Function: Validate Dates
    function validateDates(arrivalDate, leavingDate) {
        const isoDateFormat = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/;

        if (!arrivalDate || !leavingDate) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Dates',
                text: 'Please select both arrival and leaving dates.',
            });
            return false;
        }

        if (!isoDateFormat.test(arrivalDate) || !isoDateFormat.test(leavingDate)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Date Format',
                text: 'Dates must be in the format YYYY-MM-DDTHH:mm.',
            });
            return false;
        }

        if (new Date(leavingDate) <= new Date(arrivalDate)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Dates',
                text: 'Leaving date must be after the arrival date.',
            });
            return false;
        }

        return true;
    }

    // Function: Send Request
    async function sendRequest(formData, url) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            return response.json();
        } catch (error) {
            console.error('Request Error:', error);
            throw error;
        }
    }

    // Function: Handle Errors
    function handleError(error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Unable to process the request. Please try again later.',
        });
    }

    // Function: Book Now
    function bookNow(formData) {
        sendRequest(formData, '../Database/booking.php')
            .then((data) => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Booking Confirmed',
                        text: data.message,
                        confirmButtonText: 'View Receipt',
                    }).then(() => {
                        window.location.href = 'receipt.php'; // Adjust redirection URL
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Booking Failed',
                        text: data.message,
                    });
                }
            })
            .catch((error) => handleError(error));
    }
});

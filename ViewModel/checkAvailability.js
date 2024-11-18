document.addEventListener('DOMContentLoaded', function () {
    const bookingForm = document.getElementById('bookingForm');
    const checkAvailabilityButton = document.querySelector('.availability-btn');
    const bookNowButton = document.querySelector('.submit-btn');
    const API_URL = '../Model/booking.php'; // Centralized URL

    // Event: Check Availability
    checkAvailabilityButton.addEventListener('click', async function (event) {
        event.preventDefault();

        // Validate dates
        const arrivalDate = document.getElementById('arrival_date').value.trim();
        const leavingDate = document.getElementById('leaving_date').value.trim();

        if (!validateDates(arrivalDate, leavingDate)) return;

        // Disable button to prevent duplicate clicks
        checkAvailabilityButton.disabled = true;

        try {
            // Prepare form data
            const formData = new FormData(bookingForm);
            formData.append('action', 'check_availability');

            // Check availability
            const data = await sendRequest(formData, API_URL);
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
                        bookNow(formData); // Use updated bookNow function
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Not Available',
                    text: data.message,
                });
            }
        } catch (error) {
            handleError(error);
        } finally {
            checkAvailabilityButton.disabled = false; // Re-enable button
        }
    });

    // Event: Book Now
    bookNowButton.addEventListener('click', async function (event) {
        event.preventDefault();

        // Validate dates
        const arrivalDate = document.getElementById('arrival_date').value.trim();
        const leavingDate = document.getElementById('leaving_date').value.trim();

        if (!validateDates(arrivalDate, leavingDate)) return;

        // Disable button to prevent duplicate clicks
        bookNowButton.disabled = true;

        try {
            const formData = new FormData(bookingForm);
            formData.append('action', 'book_now');
            await bookNow(formData); // Use updated bookNow function
        } catch (error) {
            handleError(error);
        } finally {
            bookNowButton.disabled = false; // Re-enable button
        }
    });

    // Function: Validate Dates
    function validateDates(arrivalDate, leavingDate) {
        if (!arrivalDate || !leavingDate) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Dates',
                text: 'Please select both arrival and leaving dates.',
            });
            return false;
        }
    
        const arrival = new Date(arrivalDate);
        const leaving = new Date(leavingDate);
        if (leaving <= arrival) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Dates',
                text: 'Leaving date must be after arrival date.',
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

            return await response.json();
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
    async function bookNow(formData) {
        try {
            formData.set('action','book_now');
            const data = await sendRequest(formData, API_URL);
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Booking Confirmed',
                    text: data.message,
                    confirmButtonText: 'View Receipt',
                }).then(() => {
                    window.location.href = 'receipt.php'; // Redirect to receipt page
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Booking Failed',
                    text: data.message,
                });
            }
        } catch (error) {
            handleError(error);
        }
    }
});

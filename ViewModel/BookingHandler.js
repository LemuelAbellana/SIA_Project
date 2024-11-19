class BookingHandler {
    constructor(bookingFormId, checkAvailabilityButtonClass, bookNowButtonClass, apiUrl) {
        this.bookingForm = document.getElementById(bookingFormId);
        this.checkAvailabilityButton = document.querySelector(checkAvailabilityButtonClass);
        this.bookNowButton = document.querySelector(bookNowButtonClass);
        this.API_URL = apiUrl;
        
        this.init();
    }

    init() {
        this.checkAvailabilityButton.addEventListener('click', (event) => this.handleCheckAvailability(event));
        this.bookNowButton.addEventListener('click', (event) => this.handleBookNow(event));
    }

    async handleCheckAvailability(event) {
        event.preventDefault();

        // Validate dates
        const arrivalDate = document.getElementById('arrival_date').value.trim();
        const leavingDate = document.getElementById('leaving_date').value.trim();

        if (!this.validateDates(arrivalDate, leavingDate)) return;

        // Disable button to prevent duplicate clicks
        this.checkAvailabilityButton.disabled = true;

        try {
            // Prepare form data
            const formData = new FormData(this.bookingForm);
            formData.append('action', 'check_availability');

            // Check availability
            const data = await this.sendRequest(formData);
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
                        this.bookNow(formData); // Use updated bookNow function
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
            this.handleError(error);
        } finally {
            this.checkAvailabilityButton.disabled = false; // Re-enable button
        }
    }

    async handleBookNow(event) {
        event.preventDefault();

        // Validate dates
        const arrivalDate = document.getElementById('arrival_date').value.trim();
        const leavingDate = document.getElementById('leaving_date').value.trim();

        if (!this.validateDates(arrivalDate, leavingDate)) return;

        // Disable button to prevent duplicate clicks
        this.bookNowButton.disabled = true;

        try {
            const formData = new FormData(this.bookingForm);
            formData.append('action', 'book_now');
            await this.bookNow(formData); // Use updated bookNow function
        } catch (error) {
            this.handleError(error);
        } finally {
            this.bookNowButton.disabled = false; // Re-enable button
        }
    }

    // Function: Validate Dates
    validateDates(arrivalDate, leavingDate) {
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
    async sendRequest(formData) {
        try {
            const response = await fetch(this.API_URL, {
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
    handleError(error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Unable to process the request. Please try again later.',
        });
    }

    // Function: Book Now
    async bookNow(formData) {
        try {
            formData.set('action', 'book_now');
            const data = await this.sendRequest(formData);
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
            this.handleError(error);
        }
    }
}

// Instantiate the BookingHandler class
document.addEventListener('DOMContentLoaded', function () {
    new BookingHandler('bookingForm', '.availability-btn', '.submit-btn', '../Model/booking.php');
});

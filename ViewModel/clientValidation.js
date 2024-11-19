class ClientValidation {
    constructor(formId, checkAvailabilityBtnId, bookNowBtnId, errorMessagesId, apiUrl) {
        this.bookingForm = document.getElementById(formId);
        this.checkAvailabilityButton = document.getElementById(checkAvailabilityBtnId);
        this.bookNowButton = document.getElementById(bookNowBtnId);
        this.errorMessages = document.getElementById(errorMessagesId) || this.createErrorDiv();
        this.apiUrl = apiUrl;

        this.init();
    }

    init() {
        this.checkAvailabilityButton.addEventListener('click', (event) => this.handleCheckAvailability(event));
        this.bookNowButton.addEventListener('click', (event) => this.handleBookNow(event));
    }

    // Handle Check Availability click
    async handleCheckAvailability(event) {
        event.preventDefault();

        const contactNumberField = document.getElementById('contact_number');
        const numberOfPeopleField = document.getElementById('number_of_people');
        
        if (!this.validateContactNumber(contactNumberField.value) || !this.validateNumberOfPeople(numberOfPeopleField.value)) {
            this.showError("Contact number must be 11 digits and number of people must be between 1 and 1000.");
            return;
        }

        const arrivalDate = document.getElementById('arrival_date').value.trim();
        const leavingDate = document.getElementById('leaving_date').value.trim();

        if (!this.validateDates(arrivalDate, leavingDate)) return;

        // Disable button to prevent duplicate clicks
        this.checkAvailabilityButton.disabled = true;

        const formData = new FormData(this.bookingForm);
        formData.append('action', 'check_availability');

        try {
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
                        this.bookNow(formData); // Proceed to booking
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
            this.checkAvailabilityButton.disabled = false;
        }
    }

    // Handle Book Now click
    async handleBookNow(event) {
        event.preventDefault();

        const contactNumberField = document.getElementById('contact_number');
        const numberOfPeopleField = document.getElementById('number_of_people');
        
        if (!this.validateContactNumber(contactNumberField.value) || !this.validateNumberOfPeople(numberOfPeopleField.value)) {
            this.showError("Contact number must be 11 digits and number of people must be between 1 and 1000.");
            return;
        }

        const arrivalDate = document.getElementById('arrival_date').value.trim();
        const leavingDate = document.getElementById('leaving_date').value.trim();

        if (!this.validateDates(arrivalDate, leavingDate)) return;

        // Disable button to prevent duplicate clicks
        this.bookNowButton.disabled = true;

        const formData = new FormData(this.bookingForm);
        formData.append('action', 'book_now');

        try {
            const data = await this.sendRequest(formData);
            if (data.success) {
                Swal.fire('Success', data.message, 'success').then(() => {
                    window.location.href = data.redirect;
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            this.handleError(error);
        } finally {
            this.bookNowButton.disabled = false;
        }
    }

    // Send request using fetch
    async sendRequest(formData) {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'An error occurred');
            }

            return data;
        } catch (error) {
            throw new Error(error.message || 'An error occurred');
        }
    }

    validateContactNumber(contactNumber) {
        return /^\d{11}$/.test(contactNumber);
    }

    validateNumberOfPeople(numberOfPeople) {
        const num = parseInt(numberOfPeople, 10);
        return num >= 1 && num <= 1000;
    }

    showError(message) {
        this.errorMessages.innerHTML = `<p style="color: red">${message}</p>`;
    }

    validateDates(arrivalDate, leavingDate) {
        const isoDateFormat = /^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2})?$/;

        if (!arrivalDate || !leavingDate) {
            Swal.fire('Error', 'Both arrival and leaving dates are required.', 'error');
            return false;
        }

        if (!isoDateFormat.test(arrivalDate) || !isoDateFormat.test(leavingDate)) {
            Swal.fire('Error', 'Dates must be in the correct format (YYYY-MM-DD or YYYY-MM-DDTHH:mm).', 'error');
            return false;
        }

        if (new Date(arrivalDate) >= new Date(leavingDate)) {
            Swal.fire('Error', 'Leaving date must be after arrival date.', 'error');
            return false;
        }

        return true;
    }

    handleError(error) {
        Swal.fire('Error', error.message, 'error');
        console.error(error);
    }

    createErrorDiv() {
        const newErrorDiv = document.createElement('div');
        newErrorDiv.id = 'errorMessages';
        document.body.insertBefore(newErrorDiv, this.bookingForm);
        return newErrorDiv;
    }
}

// Instantiate the class when the document is ready
document.addEventListener('DOMContentLoaded', () => {
    new ClientValidation('bookingForm', 'checkAvailabilityBtn', 'bookNowBtn', 'errorMessages', 'Booking.php');
});

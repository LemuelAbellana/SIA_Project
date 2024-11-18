document.addEventListener('DOMContentLoaded', () => {
    const bookingForm = document.getElementById('bookingForm');
    const checkAvailabilityButton = document.getElementById('checkAvailabilityBtn');
    const bookNowButton = document.getElementById('bookNowBtn');
    const contactNumberField = document.getElementById('contact_number');
    const numberOfPeopleField = document.getElementById('number_of_people');
    const errorMessages = document.getElementById('errorMessages'); // The div for error messages

    // Create a div for displaying error messages if it doesn't exist
    if (!errorMessages) {
        const newErrorDiv = document.createElement('div');
        newErrorDiv.id = 'errorMessages';
        document.body.insertBefore(newErrorDiv, bookingForm);
    }

    // Event listener for Check Availability button
    checkAvailabilityButton.addEventListener('click', async function (event) {
        event.preventDefault();

        // Validate contact number and number of people
        if (!validateContactNumber(contactNumberField.value) || !validateNumberOfPeople(numberOfPeopleField.value)) {
            showError("Contact number must be 11 digits and number of people must be between 1 and 1000.");
            return;
        }

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

            // Check availability (API request)
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
                        bookNow(formData); // Proceed to booking
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
            checkAvailabilityButton.disabled = false;
        }
    });

    // Event listener for Book Now button
    bookNowButton.addEventListener('click', async function (event) {
        event.preventDefault();

        // Validate contact number and number of people
        if (!validateContactNumber(contactNumberField.value) || !validateNumberOfPeople(numberOfPeopleField.value)) {
            showError("Contact number must be 11 digits and number of people must be between 1 and 1000.");
            return;
        }

        // Validate dates
        const arrivalDate = document.getElementById('arrival_date').value.trim();
        const leavingDate = document.getElementById('leaving_date').value.trim();

        if (!validateDates(arrivalDate, leavingDate)) return;

        // Disable button to prevent duplicate clicks
        bookNowButton.disabled = true;

        try {
            // Prepare form data
            const formData = new FormData(bookingForm);
            formData.append('action', 'book_now');

            // Send the booking request
            const data = await sendRequest(formData, API_URL);
            if (data.success) {
                Swal.fire('Success', data.message, 'success').then(() => {
                    window.location.href = data.redirect; // Redirect to receipt page
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            handleError(error);
        } finally {
            bookNowButton.disabled = false;
        }
    });

    // Helper function to validate the contact number
    function validateContactNumber(contactNumber) {
        return /^\d{11}$/.test(contactNumber); // Must be exactly 11 digits
    }

    // Helper function to validate the number of people
    function validateNumberOfPeople(numberOfPeople) {
        const num = parseInt(numberOfPeople, 10);
        return num >= 1 && num <= 1000; // Must be between 1 and 1000
    }

    // Function to show error message
    function showError(message) {
        errorMessages.innerHTML = `<p style="color: red">${message}</p>`;
    }

    // Function to validate dates
    function validateDates(arrivalDate, leavingDate) {
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

        return true; // Validation passed
    }

    // Dummy function to simulate the API request
    async function sendRequest(formData, apiUrl) {
        // Simulate sending data to the server and getting a response
        return new Promise((resolve) => {
            setTimeout(() => {
                resolve({
                    success: true,
                    message: "Dates are available!",
                    redirect: "receipt.php"
                });
            }, 1000);
        });
    }

    // Function to handle errors (generic)
    function handleError(error) {
        Swal.fire('Error', 'There was a problem processing your request.', 'error');
        console.error(error);
    }
});

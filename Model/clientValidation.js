document.addEventListener('DOMContentLoaded', () => {
    const bookingForm = document.getElementById('bookingForm');
    const submitButton = bookingForm.querySelector('[type="submit"]');

    // Event listener for form submission
    bookingForm.addEventListener('submit', (e) => {
        const arrivalDate = document.getElementById('arrival_date').value.trim();
        const leavingDate = document.getElementById('leaving_date').value.trim();

        // Validate dates
        if (!validateDates(arrivalDate, leavingDate)) {
            e.preventDefault(); // Prevent form submission if validation fails
            return;
        }

        // Disable the submit button to prevent duplicate submissions
        submitButton.disabled = true;
    });

    // Function to validate dates
    function validateDates(arrivalDate, leavingDate) {
        const isoDateFormat = /^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2})?$/; // Matches both date and datetime-local

        // Check for empty values
        if (!arrivalDate || !leavingDate) {
            Swal.fire('Error', 'Both arrival and leaving dates are required.', 'error');
            return false;
        }

        // Check for proper format
        if (!isoDateFormat.test(arrivalDate) || !isoDateFormat.test(leavingDate)) {
            Swal.fire('Error', 'Dates must be in the correct format (YYYY-MM-DD or YYYY-MM-DDTHH:mm).', 'error');
            return false;
        }

        // Check date logic
        if (new Date(arrivalDate) >= new Date(leavingDate)) {
            Swal.fire('Error', 'Leaving date must be after arrival date.', 'error');
            return false;
        }

        return true; // Validation passed
    }
});

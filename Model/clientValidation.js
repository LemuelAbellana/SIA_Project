document.addEventListener('DOMContentLoaded', () => {
    const bookingForm = document.getElementById('bookingForm');

    bookingForm.addEventListener('submit', (e) => {
        const arrivalDate = document.getElementById('arrival_date').value.trim();
        const leavingDate = document.getElementById('leaving_date').value.trim();

        if (!arrivalDate || !leavingDate) {
            Swal.fire('Error', 'Both arrival and leaving dates are required.', 'error');
            e.preventDefault();
            return;
        }

        if (new Date(arrivalDate) >= new Date(leavingDate)) {
            Swal.fire('Error', 'Leaving date must be after arrival date.', 'error');
            e.preventDefault();
            return;
        }
    });
});

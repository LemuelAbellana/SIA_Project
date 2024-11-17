document.getElementById('bookingForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const formData = new FormData(event.target);
    const action = formData.get('action');

    try {
        const response = await fetch(`../Database/BookingHandler.php`, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            Swal.fire('Success', data.message, 'success');
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    } catch (error) {
        Swal.fire('Oops...', 'Something went wrong. Please try again later.', 'error');
    }
});

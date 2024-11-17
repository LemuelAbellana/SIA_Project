class ConfirmCancel {
    constructor(bookingId, arrivalDateStr) {
        this.bookingId = bookingId;
        this.arrivalDate = new Date(arrivalDateStr);
        this.currentDate = new Date();
    }

    canCancel() {
        const twoDaysBeforeArrival = new Date(this.arrivalDate);
        twoDaysBeforeArrival.setDate(this.arrivalDate.getDate() - 2);

        return this.currentDate < twoDaysBeforeArrival;
    }

    confirmCancellation() {
        if (!this.canCancel()) {
            Swal.fire({
                icon: 'error',
                title: 'Cancellation Not Allowed',
                text: 'You can only cancel the booking at least 2 days before the arrival date.',
            });
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you really want to cancel your booking?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, cancel it!'
        }).then((result) => {
            if (result.isConfirmed) {
                this.performCancellation();
            }
        });
    }

    performCancellation() {
        const formData = new FormData();
        formData.append("booking_id", this.bookingId);

        fetch('cancelBooking.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    icon: data.success ? 'success' : 'error',
                    title: data.success ? 'Booking Canceled' : 'Error',
                    text: data.message,
                });
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Something went wrong',
                    text: 'Please try again later.',
                });
            });
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const cancelButton = document.querySelector(".cancel-button");
    
    // Ensure the cancel button exists
    if (!cancelButton) return;

    // Get the booking ID and arrival date from data attributes
    const bookingId = cancelButton.getAttribute("data-booking-id");
    const arrivalDate = cancelButton.getAttribute("data-arrival-date");

    // Handle cancel button click
    cancelButton.addEventListener("click", () => {
        const cancelHandler = new ConfirmCancel(bookingId, arrivalDate);
        cancelHandler.confirmCancellation();
    });
});

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
                icon: "error",
                title: "Cancellation Not Allowed",
                text: "Cancellations are only allowed at least 2 days before the arrival date.",
            });
            return;
        }

        Swal.fire({
            title: "Are you sure?",
            text: "Do you really want to cancel your booking?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, cancel it!",
            cancelButtonText: "No, keep it",
        }).then((result) => {
            if (result.isConfirmed) {
                this.performCancellation();
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire({
                    icon: "info",
                    title: "Cancelled",
                    text: "Your booking has not been canceled.",
                });
            }
        });
    }

    performCancellation() {
        const formData = new FormData();
        formData.append("booking_id", this.bookingId);

        fetch("../Model/cancelBooking.php", { method: "POST", body: formData })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then((data) => {
                Swal.fire({
                    icon: data.success ? "success" : "error",
                    title: data.success ? "Booking Canceled" : "Error",
                    text: data.message,
                }).then(() => {
                    if (data.success) {
                        location.href = "booking.html";
                    }
                });
            })
            .catch((error) => {
                Swal.fire({
                    icon: "error",
                    title: "Something went wrong",
                    text: "Please try again later.",
                });
                console.error("Error:", error);
            });
    }
}

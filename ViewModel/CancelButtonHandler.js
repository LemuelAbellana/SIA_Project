document.addEventListener("DOMContentLoaded", () => {
    const cancelButtonHandler = new CancelButtonHandler(".cancel-button");
    cancelButtonHandler.init();
});

class CancelButtonHandler {
    constructor(cancelButtonSelector) {
        this.cancelButtonSelector = cancelButtonSelector;
    }

    init() {
        const cancelButtons = document.querySelectorAll(this.cancelButtonSelector);

        if (cancelButtons.length === 0) {
            console.warn("No cancel buttons found. Ensure the HTML contains elements with the class '.cancel-button'.");
            return;
        }

        cancelButtons.forEach((cancelButton) => {
            const bookingId = cancelButton.getAttribute("data-booking-id");

            if (!bookingId) {
                console.warn("Cancel button is missing the 'data-booking-id' attribute.");
                return;
            }

            cancelButton.addEventListener("click", () => {
                this.fetchBookingDetails(bookingId)
                    .then((details) => {
                        const cancelHandler = new ConfirmCancel(details);
                        cancelHandler.confirmCancellation();
                    })
                    .catch((error) => {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Failed to retrieve booking details. Please try again later.",
                        });
                        console.error("Error fetching booking details:", error);
                    });
            });
        });
    }

    async fetchBookingDetails(bookingId) {
        try {
            const formData = new FormData();
            formData.append("action", "get_details");
            formData.append("booking_id", bookingId);

            const response = await fetch("../Model/cancelBooking.php", {
                method: "POST",
                body: formData,
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || "Failed to fetch booking details.");
            }

            return data.details;
        } catch (error) {
            throw error;
        }
    }
}

class ConfirmCancel {
    constructor(details) {
        this.details = details;
        this.bookingId = details.booking_id;
        this.canCancel = details.can_cancel; // Ensure this field is available from the backend.
    }

    confirmCancellation() {
        if (!this.canCancel) {
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
                this.   performCancellation();
            }
        });
    }

    async performCancellation() {
        try {
            const formData = new FormData();
            formData.append("action", "cancel_booking");
            formData.append("booking_id", this.bookingId);
    
            const response = await fetch("../Model/cancelBooking.php", {
                method: "POST",
                body: formData,
            });
    
            const data = await response.json();
            Swal.fire({
                icon: data.success ? "success" : "error",
                title: data.success ? "Booking Canceled" : "Error",
                text: data.message,
            }).then(() => {
                if (data.success) {
                    // Redirect to booking.html after successful cancellation
                    window.location.href = "booking.html";
                }
            });
        } catch (error) {
            Swal.fire({
                icon: "error",
                title: "Something went wrong",
                text: "Please try again later.",
            });
            console.error("Error during cancellation:", error);
        }
    }
    
}
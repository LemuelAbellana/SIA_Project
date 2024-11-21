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

        cancelButtons.forEach((cancelButton) => {
            const bookingId = cancelButton.getAttribute("data-booking-id");

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
        this.canCancel = details.can_cancel; // Assuming `can_cancel` is part of the backend response.
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
                this.performCancellation();
            } else {
                Swal.fire({
                    icon: "info",
                    title: "Cancelled",
                    text: "Your booking has not been canceled.",
                });
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
                    location.href = "booking.html"; // Reload the page or redirect
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

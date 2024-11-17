class ConfirmCancel {
    constructor(bookingId, arrivalDateStr) {
      this.bookingId = bookingId;
      this.arrivalDateStr = arrivalDateStr;
    }
  
    confirmCancellation() {
      if (!bookingId || bookingId === '') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Invalid booking ID.',
        });
        return;
    }
  
    // Parse the arrival date (no time component)
    const arrivalDate = new Date(arrivalDateStr);
    const currentDate = new Date();
    const twoDaysBeforeArrival = new Date(arrivalDate);
    twoDaysBeforeArrival.setDate(arrivalDate.getDate() - 2);  // Subtract 2 days
  
    // Normalize the dates to compare only the date part (ignoring the time)
    arrivalDate.setHours(0, 0, 0, 0);
    currentDate.setHours(0, 0, 0, 0);
    twoDaysBeforeArrival.setHours(0, 0, 0, 0);
  
    // Check if cancellation is allowed
    if (currentDate >= twoDaysBeforeArrival) {
        Swal.fire({
            icon: 'error',
            title: 'Cancellation Not Allowed',
            text: 'You can only cancel the booking at least 2 days before the arrival date.',
        });
        return;
    }
  
    // First confirmation: Ask if they really want to cancel
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you really want to cancel your booking? This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, cancel it!',
        cancelButtonText: 'No, keep it',
    }).then((result) => {
        if (result.isConfirmed) {
            // If the user confirmed, proceed with cancellation
            const formData = new FormData();
            formData.append("booking_id", bookingId);
  
            fetch('cancelBooking.php', {
              method: 'POST',
              body: formData
          })
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  Swal.fire({
                      icon: 'success',
                      title: 'Booking Canceled',
                      text: data.message,
                  }).then(() => {
                      window.location.href = '../view/myBooking.html'; // Redirect to booking page or reload
                  });
              } else {
                  Swal.fire({
                      icon: 'error',
                      title: 'Error',
                      text: data.message,
                  });
              }
          })
          .catch(error => {
              Swal.fire({
                  icon: 'error',
                  title: 'Something went wrong',
                  text: 'Please try again later.',
              });
          });
        } else {
            Swal.fire({
                icon: 'info',
                title: 'Cancelled',
                text: 'Your booking was not canceled.',
            });
        }
    });
    }
  }
  
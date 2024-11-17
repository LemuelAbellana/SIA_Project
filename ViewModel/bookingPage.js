class BookingPage {
  constructor(formElement) {
      this.formElement = formElement;
      this.bindEvents();
  }

  bindEvents() {
      const submitButton = document.querySelector('.submit-btn');
      const availabilityButton = document.querySelector('.availability-btn');

      submitButton.addEventListener('click', this.submitBooking.bind(this));
      availabilityButton.addEventListener('click', this.checkAvailability.bind(this));
  }

  // Handle form submission for booking
  submitBooking(event) {
      event.preventDefault();
      const formData = new FormData(this.formElement);

      fetch('../Model/booking.php', {
          method: 'POST',
          body: formData,
      })
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  Swal.fire('Success', data.message, 'success');
              } else {
                  Swal.fire('Error', data.message, 'error');
              }
          })
          .catch(error => Swal.fire('Error', 'An error occurred. Please try again later.', 'error'));
  }

  // Handle availability checking
  checkAvailability(event) {
      event.preventDefault();
      const formData = new FormData(this.formElement);

      fetch('../Model/booking.php?action=check_availability', {
          method: 'POST',
          body: formData,
      })
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  Swal.fire('Available', data.message, 'success');
              } else {
                  Swal.fire('Unavailable', data.message, 'error');
              }
          })
          .catch(error => Swal.fire('Error', 'Could not check availability.', 'error'));
  }
}

// Initialize BookingPage
document.addEventListener('DOMContentLoaded', () => {
  const bookingForm = document.getElementById('bookingForm');
  new BookingPage(bookingForm);
});

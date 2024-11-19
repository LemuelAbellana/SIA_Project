<?php
session_start();

// Redirect to booking page if no booking details are found
if (!isset($_SESSION['booking_details'])) {
    header("Location: booking.html");
    exit;
}

// Retrieve booking details from the session
$bookingDetails = $_SESSION['booking_details'];

// Sanitize data for HTML output
$booking_id = htmlspecialchars($bookingDetails['booking_id'] ?? 'N/A');
$name = htmlspecialchars($bookingDetails['name'] ?? 'N/A');
$email = htmlspecialchars($bookingDetails['email'] ?? 'N/A');
$contact_number = htmlspecialchars($bookingDetails['contact_number'] ?? 'N/A');
$event_type = htmlspecialchars($bookingDetails['event_type'] ?? 'N/A');
$number_of_people = htmlspecialchars($bookingDetails['number_of_people'] ?? 'N/A');
$arrival_date = htmlspecialchars($bookingDetails['arrival_date'] ?? 'N/A');
$leaving_date = htmlspecialchars($bookingDetails['leaving_date'] ?? 'N/A');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Booking details and cancellation for Escape Avenue.">
  <meta name="author" content="Escape Avenue">
  <title>Booking Receipt</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
    crossorigin="anonymous" referrerpolicy="no-referrer">
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>

<body>
  <header class="header">
    <div class="navigation-bar">
      <div class="nav-links">
        <a href="./index.html">Home</a>
        <a href="./services.html">Services</a>
        <a href="./contact.html">Contact</a>
        <a href="./index.html"><img src="../Assets/SIA_LOGO_wobg1.png" alt="Escape Avenue" class="logo"></a>
        <a href="./booking.html">Book Now</a>
        <a href="./myBooking.html">My Booking</a>
        <a href="./faq.html">FAQ</a>
      </div>
    </div>
  </header>

  <img class="homepage-image" src="../Assets/my_booking.png" alt="My Booking">

  <div class="receipt-container">
    <h2 class="title-booking">Booking Receipt</h2>
    <div class="receipt-row">
      <strong>ID Number:</strong> <span class="receipt-value"><?php echo $booking_id; ?></span>
    </div>
    <div class="receipt-row">
      <strong>Name:</strong> <span class="receipt-value"><?php echo $name; ?></span>
    </div>
    <div class="receipt-row">
      <strong>Email:</strong> <span class="receipt-value"><?php echo $email; ?></span>
    </div>
    <div class="receipt-row">
      <strong>Contact Number:</strong> <span class="receipt-value"><?php echo $contact_number; ?></span>
    </div>
    <div class="receipt-row">
      <strong>Event Type:</strong> <span class="receipt-value"><?php echo $event_type; ?></span>
    </div>
    <div class="receipt-row">
      <strong>Number of People:</strong> <span class="receipt-value"><?php echo $number_of_people; ?></span>
    </div>
    <div class="receipt-row">
      <strong>Arrival Date:</strong> <span class="receipt-value"><?php echo $arrival_date; ?></span>
    </div>
    <div class="receipt-row">
      <strong>Leaving Date:</strong> <span class="receipt-value"><?php echo $leaving_date; ?></span>
    </div>
    <div class="receipt-row">
      <strong>Status:</strong> <span class="receipt-value"><strong class="confirm">Confirmed</strong></span>
    </div>
    <div class="note">
      <strong>Note:</strong> <span>Thank you for booking with Escape Avenue!</span> <span>Please take a screenshot of your receipt</span>
    </div>
    <form id="cancelForm">
      <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
      <button type="button" class="cancel-button" 
        data-booking-id="<?php echo $booking_id; ?>" 
        data-arrival-date="<?php echo $arrival_date; ?>">Cancel Booking</button>
    </form>
  </div>

  <footer class="footer">
    <div class="footer-container">
      <div class="footer-left">
        <p>&copy; 2024 Escape Avenue. All rights reserved.</p>
      </div>
      <div class="footer-middle">
        <a href="./index.html" class="footer-logo">
          <img src="../Assets/footer_logo.png" alt="Escape Avenue Logo" class="footer-logo-image">
        </a>
      </div>
      <div class="footer-right">
        <p>Follow us:</p>
        <a href="https://facebook.com" target="_blank" aria-label="Follow us on Facebook"><i class="fab fa-facebook"></i></a>
        <a href="https://instagram.com" target="_blank" aria-label="Follow us on Instagram"><i class="fab fa-instagram"></i></a>
        <a href="https://twitter.com" target="_blank" aria-label="Follow us on Twitter"><i class="fab fa-twitter"></i></a>
      </div>
    </div>
  </footer>

  <script src="../ViewModel/CancelButtonHandler.js"></script>
</body>
</html>

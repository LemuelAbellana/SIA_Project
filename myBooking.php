<?php
session_start();

if (isset($_SESSION['booking_id'])) {
    $booking_id = $_SESSION['booking_id'];
    $name = $_SESSION['name'];
    $email = $_SESSION['email'];
    $contact_number = $_SESSION['contact_number'];
    $event_type = $_SESSION['event_type'];
    $number_of_people = $_SESSION['number_of_people'];
    $arrival_date = $_SESSION['arrival_date'];
    $leaving_date = $_SESSION['leaving_date'];

    // Clear session after displaying booking details
    session_unset();
    session_destroy();
} else {
    echo "No booking information found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
    crossorigin="anonymous" referrerpolicy="no-referrer">
  <title>Booking Receipt</title>
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
        <a href="index.html"><img src="../SIA_Project/Assets/SIA_LOGO_wobg1.png" alt="Escape Avenue" class="logo"></a>
        <a href="./booking.html">Book Now</a>
        <a href="./myBooking.html">My Booking</a>
      </div>
      <div class="dropdown">
        <a href="#" class="dropdown-btn">Sign Up</a>
        <div class="dropdown-content">
          <a href="./register.html"><i class="fa-solid fa-user-plus"></i> Register</a>
          <a href="./login.html"><i class="fa-solid fa-user"></i> Login</a>
        </div>
      </div>
    </div>
  </header>

  <h2 class="title-booking">Booking Receipt</h2>
  
  <div class="receipt-container">
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
      <strong>Status:</strong> <span class="receipt-value"> <strong>Confirmed</strong></span>
    </div>
    <div class="note">
      <strong>Note:</strong> <span>Thank you for booking with Escape Avenue!</span>
    </div>
    <form id="cancelForm" action="cancelBooking.php" method="POST">
      <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
      <button type="button" class="cancel-button" onclick="confirmCancellation()">Cancel Booking</button>
    </form>
  </div>
  </div>
  
    


  <footer class="footer">
    <div class="footer-container">
      <div class="footer-left">
        <p>&copy; 2024 Escape Avenue. All rights reserved.</p>
      </div>
      <div class="footer-middle">
        <a href="./index.html" class="footer-logo">
          <img src="../SIA_Project/Assets/footer_logo.png" alt="Escape Avenue Logo" class="footer-logo-image">
        </a>
      </div>
      <div class="footer-right">
        <p>Follow us:</p>
        <a href="https://facebook.com" target="_blank"><i class="fab fa-facebook"></i></a>
        <a href="https://instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="https://twitter.com" target="_blank"><i class="fab fa-twitter"></i></a>
      </div>
    </div>
  </footer>
  <script src="confirm_cancel.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
    crossorigin="anonymous" referrerpolicy="no-referrer">
  <link rel="stylesheet" href="style.css">
  <title>Booking</title>
</head>

<body>

  <header class="header">
    <div class="navigation-bar">
      <div class="nav-links">
        <a href="./index.html">Home</a>
        <a href="./services.html">Services</a>
        <a href="./contact.html">Contact</a>
        <a href="./index.html"><img src="../SIA_Project/Assets/SIA_LOGO_wobg1.png" alt="Escape Avenue" class="logo"></a>
        <a href="./booking.html">Book Now</a>
      </div>
      <div class="dropdown">
        <a href="./services.html" class="dropdown-btn">Sign Up</a>
        <div class="dropdown-content">
          <a href="./register.html"><i class="fa-solid fa-user-plus"></i> Register</a>
          <a href="./login.html"><i class="fa-solid fa-user"></i> Login</a>
        </div>
      </div>
    </div>
  </header>

  <div class="image-header">
    <div class="image-overlay">
      <div class="welcome">
        <img src="../SIA_Project/Assets/Reservation.png" alt="Escape Avenue" class="h4">
      </div>
    </div>
  </div>

  <section class="booking">
    <h1 class="booking-heading">
      <span>B</span><span>O</span><span>O</span><span>K</span><span class="space"></span>
      <span>N</span><span>O</span><span>W</span>
    </h1>

    <div class="booking-row">
      <div class="booking-img">
        <img src="../SIA_Project/Assets/avenuegraphic.png" alt="Graphical Representation of Escape Avenue">
      </div>
      <form action="process_form.php" method="post">
        <div class="form-input-box">
          <label for="name"><h3>Name:</h3></label>
          <input type="text" id="name" name="name" placeholder="First name" required>
        </div>

        <div class="form-input-box">
          <label for="email"><h3>Email:</h3></label>
          <input type="email" id="email" name="email" placeholder="sample123@gmail.com" required>
        </div>

        <div class="form-input-box">
          <label for="contact_number"><h3>Contact Number:</h3></label>
          <input type="tel" id="contact_number" name="contact_number" placeholder="09512575802" required>
        </div>

        <div class="form-input-box">
          <label for="event_type"><h3>Event Type:</h3></label>
          <input type="text" id="event_type" name="event_type" placeholder="Birthday Party" required>
        </div>

        <div class="form-input-box">
          <label for="number_of_people"><h3>Number of People:</h3></label>
          <input type="number" id="number_of_people" name="number_of_people" placeholder="25pax" required>
        </div>

        <div class="form-input-box">
          <label for="arrival_date"><h3>Arrivals</h3></label>
          <input type="datetime-local" id="arrival_date" name="arrival_date" required>
        </div>

        <div class="form-input-box">
          <label for="leaving_date"><h3>Leaving</h3></label>
          <input type="datetime-local" id="leaving_date" name="leaving_date" required>
        </div>

        <div class="form-submit-box">
          <button type="submit" name="action" value="check_availability" class="availability-btn">Check Availability</button>
        </div> 

        <div class="form-submit-box">
          <button type="submit" name="action" value="book_now" class="submit-btn">Book Now</button>
        </div>
      </form>
    </div>
  </section>

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

  <script src="datedisable.js"></script>
  <?php include 'message.php'; ?>
</body>
</html>

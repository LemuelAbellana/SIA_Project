<?php
session_start();
include 'bookingdatabase.php';

$success_msg = [];
$error_msg = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $contact_number = mysqli_real_escape_string($conn, trim($_POST['contact_number']));
    $event_type = mysqli_real_escape_string($conn, trim($_POST['event_type']));
    $number_of_people = (int)$_POST['number_of_people'];
    $arrival_date = mysqli_real_escape_string($conn, $_POST['arrival_date']);
    $leaving_date = mysqli_real_escape_string($conn, $_POST['leaving_date']);
    $action = $_POST['action'];

    if (empty($name) || empty($email) || empty($contact_number) || empty($event_type) || empty($number_of_people) || empty($arrival_date) || empty($leaving_date)) {
        $error_msg[] = "Please fill in all fields.";
    } else {
        $check_query = "SELECT * FROM booking_information 
                        WHERE (arrival_date < '$leaving_date' AND leaving_date > '$arrival_date')";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $error_msg[] = "The venue is not available for the selected dates.";
        } else {
            if ($action === "check_availability") {
                $success_msg[] = "The venue is available for the selected dates.";
            } elseif ($action === "book_now") {
                mysqli_begin_transaction($conn);
                try {
                    // Retrieve or insert customer data
                    $customer_query = "SELECT customer_id FROM customer WHERE contact_number = '$contact_number'";
                    $customer_result = mysqli_query($conn, $customer_query);
                    if (mysqli_num_rows($customer_result) > 0) {
                        $customer_id = mysqli_fetch_assoc($customer_result)['customer_id'];
                    } else {
                        $insert_customer = "INSERT INTO customer (name, email, contact_number) VALUES ('$name', '$email', '$contact_number')";
                        if (!mysqli_query($conn, $insert_customer)) {
                            throw new Exception("Error adding customer: " . mysqli_error($conn));
                        }
                        $customer_id = mysqli_insert_id($conn);
                    }

                    // Retrieve or insert event data
                    $event_query = "SELECT event_id FROM event WHERE event_type = '$event_type'";
                    $event_result = mysqli_query($conn, $event_query);
                    if (mysqli_num_rows($event_result) > 0) {
                        $event_id = mysqli_fetch_assoc($event_result)['event_id'];
                    } else {
                        $insert_event = "INSERT INTO event (event_type) VALUES ('$event_type')";
                        if (!mysqli_query($conn, $insert_event)) {
                            throw new Exception("Error adding event: " . mysqli_error($conn));
                        }
                        $event_id = mysqli_insert_id($conn);
                    }

                    // Insert booking and people data
                    $insert_booking = "INSERT INTO booking_information (customer_id, event_id, arrival_date, leaving_date) 
                                       VALUES ($customer_id, $event_id, '$arrival_date', '$leaving_date')";
                    if (!mysqli_query($conn, $insert_booking)) {
                        throw new Exception("Error adding booking information: " . mysqli_error($conn));
                    }
                    $booking_id = mysqli_insert_id($conn);

                    $insert_people = "INSERT INTO number_of_people (booking_id, number_of_people) 
                                      VALUES ($booking_id, $number_of_people)";
                    if (!mysqli_query($conn, $insert_people)) {
                        throw new Exception("Error adding number of people: " . mysqli_error($conn));
                    }

                    // Commit and set session data
                    mysqli_commit($conn);
                    $_SESSION['booking_id'] = $booking_id;
                    $_SESSION['name'] = $name;
                    $_SESSION['email'] = $email;
                    $_SESSION['contact_number'] = $contact_number;
                    $_SESSION['event_type'] = $event_type;
                    $_SESSION['number_of_people'] = $number_of_people;
                    $_SESSION['arrival_date'] = $arrival_date;
                    $_SESSION['leaving_date'] = $leaving_date;

                    header("Location: myBooking.php");
                    exit();
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $error_msg[] = $e->getMessage();
                }
            }
        }
    }
    mysqli_close($conn);
}

if (!empty($success_msg)) {
    foreach ($success_msg as $msg) {
        echo "<script>
                Swal.fire({
                    title: 'Success',
                    html: '$msg',
                    icon: 'success',
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                });
              </script>";
    }
}

if (!empty($error_msg)) {
    foreach ($error_msg as $msg) {
        echo "<script>
                Swal.fire({
                    title: 'Error',
                    html: '$msg',
                    icon: 'error',
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                });
              </script>";
    }
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
  <link rel="stylesheet" href="style.css">
  <title>Booking</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>

<body>

  <header class="header">
    <div class="navigation-bar">
      <div class="nav-links">
        <a href="./index.html">Home</a>
        <a href="./services.html">Services</a>
        <a href="./contact.html">Contact</a>
        <a href="./index.html"><img src="../SIA_Project/Assets/SIA_LOGO_wobg1.png" alt="Escape Avenue" class="logo"></a>
        <a href="./booking.php">Book Now</a>
        <a href="./myBooking.php">My Booking</a>
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

      <form action="booking.php" method="post">
        <div class="form-input-box">
          <label for="name">
            <h3>Name:</h3>
          </label>
          <input type="text" id="name" name="name" placeholder="Full name" required>
        </div>
        <div class="form-input-box">
          <label for="email">
            <h3>Email:</h3>
          </label>
          <input type="email" id="email" name="email" placeholder="sample123@gmail.com" required>
        </div>
        <div class="form-input-box">
          <label for="contact_number">
            <h3>Contact Number:</h3>
          </label>
          <input type="tel" id="contact_number" name="contact_number" placeholder="09512575802" required>
        </div>
        <div class="form-input-box">
          <label for="event_type">
            <h3>Event Type:</h3>
          </label>
          <input type="text" id="event_type" name="event_type" placeholder="Birthday Party" required>
        </div>
        <div class="form-input-box">
          <label for="number_of_people">
            <h3>Number of People:</h3>
          </label>
          <input type="number" id="number_of_people" name="number_of_people" placeholder="25pax" required>
        </div>
        <div class="form-input-box">
          <label for="arrival_date">
            <h3>Arrivals</h3>
          </label>
          <input type="datetime-local" id="arrival_date" name="arrival_date" required>
        </div>
        <div class="form-input-box">
          <label for="leaving_date">
            <h3>Leaving</h3>
          </label>
          <input type="datetime-local" id="leaving_date" name="leaving_date" required>
        </div>
        <div class="form-submit-box">
          <button type="submit" name="action" value="check_availability" class="availability-btn">Check
            Availability</button>
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
</body>

</html>
<?php
session_start();
include 'bookingdatabase.php';

$response = ['success' => false, 'message' => ''];
$booking_data = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    // Sanitize and validate inputs
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $contact_number = mysqli_real_escape_string($conn, trim($_POST['contact_number']));
    $event_type = mysqli_real_escape_string($conn, trim($_POST['event_type']));
    $number_of_people = (int)$_POST['number_of_people'];
    $arrival_date = mysqli_real_escape_string($conn, $_POST['arrival_date']);
    $leaving_date = mysqli_real_escape_string($conn, $_POST['leaving_date']);

    if ($action == 'check_availability') {
        $check_query = "SELECT * FROM booking_information 
                        WHERE (arrival_date < '$leaving_date' AND leaving_date > '$arrival_date')";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $response['message'] = "The venue is not available for the selected dates.";
        } else {
            $response['success'] = true;
            $response['message'] = "The venue is available for the selected dates.";
        }
        echo json_encode($response);
        exit;
    }

    if ($action == 'book_now') {
        $check_query = "SELECT * FROM booking_information 
                        WHERE (arrival_date < '$leaving_date' AND leaving_date > '$arrival_date')";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            header("Location: ../View/booking.html?error=unavailable");
            exit;
        }

        // Proceed with booking
        mysqli_begin_transaction($conn);
        try {
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

            mysqli_commit($conn);
            $_SESSION['booking_data'] = [
                'booking_id' => $booking_id,
                'name' => $name,
                'email' => $email,
                'contact_number' => $contact_number,
                'event_type' => $event_type,
                'number_of_people' => $number_of_people,
                'arrival_date' => $arrival_date,
                'leaving_date' => $leaving_date,
                'status' => 'Confirmed'
            ];
        } catch (Exception $e) {
            mysqli_rollback($conn);
            header("Location: ../View/booking.html?error=booking_failed");
            exit;
        }
    }
}

$booking_data = $_SESSION['booking_data'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../View/style.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <title>Booking Receipt</title>
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

<?php if ($booking_data): ?>

  <img class="homepage-image" src="../Assets/my_booking.png" alt="">

  <div class="receipt-container">
    <h2>Booking Receipt</h2>
    <p><strong>ID Number:</strong> <?= $booking_data['booking_id'] ?></p>
    <p><strong>Name:</strong> <?= $booking_data['name'] ?></p>
    <p><strong>Email:</strong> <?= $booking_data['email'] ?></p>
    <p><strong>Contact Number:</strong> <?= $booking_data['contact_number'] ?></p>
    <p><strong>Event Type:</strong> <?= $booking_data['event_type'] ?></p>
    <p><strong>Number of People:</strong> <?= $booking_data['number_of_people'] ?></p>
    <p><strong>Arrival Date:</strong> <?= $booking_data['arrival_date'] ?></p>
    <p><strong>Leaving Date:</strong> <?= $booking_data['leaving_date'] ?></p>
    <p><strong>Status:</strong> <span style="color: green;"><?= $booking_data['status'] ?></span></p>
</div>
<?php endif; ?>

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
      <a href="#"><i class="fab fa-facebook"></i></a>
      <a href="#"><i class="fab fa-instagram"></i></a>
      <a href="#"><i class="fab fa-twitter"></i></a>
    </div>
  </div>
</footer>

<script src="../Model/confirm_cancel.js"></script>
</body>
</html>
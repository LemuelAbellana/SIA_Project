<?php
session_start();
require 'bookingdatabase.php'; // Ensure this file contains the correct database connection setup

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ['success' => false, 'message' => ''];
$booking_data = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_GET['action'] ?? $_POST['action'] ?? null;

    // Retrieve and sanitize inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $event_type = trim($_POST['event_type'] ?? '');
    $number_of_people = (int)($_POST['number_of_people'] ?? 0);
    $arrival_date = $_POST['arrival_date'] ?? '';
    $leaving_date = $_POST['leaving_date'] ?? '';

    if ($action === 'check_availability') {
        // Check for date overlap
        $query = "SELECT * FROM booking_information WHERE arrival_date < ? AND leaving_date > ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ss', $leaving_date, $arrival_date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $response['message'] = "The venue is not available for the selected dates.";
        } else {
            $response['success'] = true;
            $response['message'] = "The venue is available for the selected dates.";
        }
        echo json_encode($response);
        exit;
    }

    if ($action === 'book_now') {
        if (!$name || !$email || !$contact_number || !$event_type || $number_of_people <= 0 || !$arrival_date || !$leaving_date) {
            $response['message'] = "All fields are required.";
            echo json_encode($response);
            exit;
        }

        if (strtotime($arrival_date) >= strtotime($leaving_date)) {
            $response['message'] = "Arrival date must be before leaving date.";
            echo json_encode($response);
            exit;
        }

        $conn->begin_transaction();

        try {
            // Insert customer data
            $query = "INSERT INTO customer (name, email, contact_number) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('sss', $name, $email, $contact_number);
            $stmt->execute();
            $customer_id = $stmt->insert_id;

            // Insert event data
            $query = "INSERT INTO event (event_type) VALUES (?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('s', $event_type);
            $stmt->execute();
            $event_id = $stmt->insert_id;

            // Insert booking data
            $query = "INSERT INTO booking_information (customer_id, event_id, arrival_date, leaving_date) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('iiss', $customer_id, $event_id, $arrival_date, $leaving_date);
            $stmt->execute();
            $booking_id = $stmt->insert_id;

            // Insert number of people
            $query = "INSERT INTO number_of_people (booking_id, number_of_people) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ii', $booking_id, $number_of_people);
            $stmt->execute();

            $conn->commit();

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

            $response['success'] = true;
            echo json_encode($response);
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = "Booking failed. Please try again later.";
            echo json_encode($response);
            exit;
        }
    }
}

// Render receipt
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
    <div>
        <h2>Booking Details</h2>
        <p><strong>ID:</strong> <?= $booking_data['booking_id'] ?></p>
        <p><strong>Name:</strong> <?= $booking_data['name'] ?></p>
        <p><strong>Email:</strong> <?= $booking_data['email'] ?></p>
        <p><strong>Contact:</strong> <?= $booking_data['contact_number'] ?></p>
        <p><strong>Event:</strong> <?= $booking_data['event_type'] ?></p>
        <p><strong>People:</strong> <?= $booking_data['number_of_people'] ?></p>
        <p><strong>Arrival:</strong> <?= $booking_data['arrival_date'] ?></p>
        <p><strong>Leaving:</strong> <?= $booking_data['leaving_date'] ?></p>
        <p><strong>Status:</strong> <?= $booking_data['status'] ?></p>
    </div>
<?php else: ?>
    <p>No booking data found. Please try again.</p>
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
<?php
session_start();
include 'bookingdatabase.php';

$success_msg = [];
$error_msg = [];
$booking_data = $_SESSION['booking_data'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $contact_number = mysqli_real_escape_string($conn, trim($_POST['contact_number']));
    $event_type = mysqli_real_escape_string($conn, trim($_POST['event_type']));
    $number_of_people = filter_var($_POST['number_of_people'], FILTER_VALIDATE_INT);
    $arrival_date = mysqli_real_escape_string($conn, $_POST['arrival_date']);
    $leaving_date = mysqli_real_escape_string($conn, $_POST['leaving_date']);
    $action = $_POST['action'];

    // Validation
    if (
        empty($name) || !$email || empty($contact_number) || empty($event_type) ||
        empty($number_of_people) || empty($arrival_date) || empty($leaving_date)
    ) {
        $error_msg[] = "Please fill in all fields with valid data.";
    } elseif (!preg_match('/^\d{11}$/', $contact_number)) {
        $error_msg[] = "Invalid contact number. Please enter an 11-digit number.";
    } elseif ($number_of_people < 1 || $number_of_people > 1000) {
        $error_msg[] = "Invalid number of people. Enter a value between 1 and 1000.";
    } elseif ($arrival_date >= $leaving_date) {
        $error_msg[] = "Invalid date range. Leaving date must be after the arrival date.";
    } else {
        // Check availability
        $check_query = "SELECT * FROM booking_information 
                        WHERE (arrival_date < '$leaving_date' AND leaving_date > '$arrival_date')";
        $check_result = mysqli_query($conn, $check_query);

        if (!$check_result) {
            $error_msg[] = "Database error: " . mysqli_error($conn);
        } elseif (mysqli_num_rows($check_result) > 0 && $action === "book_now") {
            $error_msg[] = "The venue is not available for the selected dates.";
        } else {
            if ($action === "book_now") {
                mysqli_begin_transaction($conn);
                try {
                    // Handle customer data
                    $customer_query = "SELECT customer_id FROM customer WHERE contact_number = '$contact_number'";
                    $customer_result = mysqli_query($conn, $customer_query);
                    if (!$customer_result) {
                        throw new Exception("Database error: " . mysqli_error($conn));
                    }

                    if (mysqli_num_rows($customer_result) > 0) {
                        $customer_id = mysqli_fetch_assoc($customer_result)['customer_id'];
                    } else {
                        $insert_customer = "INSERT INTO customer (name, email, contact_number) VALUES ('$name', '$email', '$contact_number')";
                        if (!mysqli_query($conn, $insert_customer)) {
                            throw new Exception("Error adding customer: " . mysqli_error($conn));
                        }
                        $customer_id = mysqli_insert_id($conn);
                    }

                    // Handle event data
                    $event_query = "SELECT event_id FROM event WHERE event_type = '$event_type'";
                    $event_result = mysqli_query($conn, $event_query);
                    if (!$event_result) {
                        throw new Exception("Database error: " . mysqli_error($conn));
                    }

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

                    // Commit and save session data
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
                    $success_msg[] = "Booking successful!";
                    $booking_data = $_SESSION['booking_data'];
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $error_msg[] = $e->getMessage();
                }
            }
        }
    }
    mysqli_close($conn);
}
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

      <?php if (!empty($error_msg)): ?>
      <div class="error-messages">
        <?php foreach ($error_msg as $error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
        <a href="../View/booking.html">Go Back to Booking</a>
      </div>
      <?php endif; ?>

      <?php if (!empty($success_msg) && $booking_data): ?>
      <div class="receipt-container">
        <h2 class="title-booking">Booking Receipt</h2>
        <div class="receipt-row"><strong>ID Number:</strong> <?php echo htmlspecialchars($booking_data['booking_id']); ?></div>
        <div class="receipt-row"><strong>Name:</strong> <?php echo htmlspecialchars($booking_data['name']); ?></div>
        <div class="receipt-row"><strong>Email:</strong> <?php echo htmlspecialchars($booking_data['email']); ?></div>
        <div class="receipt-row"><strong>Contact Number:</strong> <?php echo htmlspecialchars($booking_data['contact_number']); ?></div>
        <div class="receipt-row"><strong>Event Type:</strong> <?php echo htmlspecialchars($booking_data['event_type']); ?></div>
        <div class="receipt-row"><strong>Number of People:</strong> <?php echo htmlspecialchars($booking_data['number_of_people']); ?></div>
        <div class="receipt-row"><strong>Arrival Date:</strong> <?php echo htmlspecialchars($booking_data['arrival_date']); ?></div>
        <div class="receipt-row"><strong>Leaving Date:</strong> <?php echo htmlspecialchars($booking_data['leaving_date']); ?></div>
        <div class="receipt-row"><strong>Status:</strong> <span style="color: green;"><?php echo htmlspecialchars($booking_data['status']); ?></span></div>
        <div class="note"><strong>Note:</strong> Thank you for booking with Escape Avenue!</div>
        <form id="cancelForm" action="cancelBooking.php" method="POST">
          <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking_data['booking_id']); ?>">
          <button type="button" class="cancel-button" onclick="confirmCancellation()">Cancel Booking</button>
        </form>
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
      <script src="../Model/clientValidation.js"></script>
    </body>

    </html>

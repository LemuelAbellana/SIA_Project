<?php
// Include the database connection
include 'bookingdatabase.php';

// Initialize booking ID (this would typically come from a session or form input)
$customer_id = $_GET['customer_id'] ?? null;

// Initialize variables
$name = $email = $contact_number = $event_type = $number_of_people = $arrival_date = $leaving_date = ""; //this means that these variables have null values
$booking_found = false;

if ($customer_id) {
    // Prepare and execute the query
    $query = "SELECT * FROM bookings WHERE customer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $name = $row['name'];
        $email = $row['email'];
        $contact_number = $row['contact_number'];
        $event_type = $row['event_type'];
        $number_of_people = $row['number_of_people'];
        $arrival_date = $row['arrival_date'];
        $leaving_date = $row['leaving_date'];
        $booking_found = true;
    } else {
        echo "<script>
                Swal.fire({
                    title: 'Error',
                    text: 'Booking not found.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
              </script>";
    }
} else {
    echo "<script>
            Swal.fire({
                title: 'Error',
                text: 'No booking ID provided.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
          </script>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Booking - Escape Avenue</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2 class="title-booking">Booking Receipt</h2>

    <?php if ($booking_found): ?>
        <div class="receipt-container">
            <div class="receipt-row"><strong>ID Number:</strong> <span><?php echo $customer_id; ?></span></div>
            <div class="receipt-row"><strong>Name:</strong> <span><?php echo htmlspecialchars($name); ?></span></div>
            <div class="receipt-row"><strong>Email:</strong> <span><?php echo htmlspecialchars($email); ?></span></div>
            <div class="receipt-row"><strong>Contact Number:</strong> <span><?php echo htmlspecialchars($contact_number); ?></span></div>
            <div class="receipt-row"><strong>Event Type:</strong> <span><?php echo htmlspecialchars($event_type); ?></span></div>
            <div class="receipt-row"><strong>Number of People:</strong> <span><?php echo htmlspecialchars($number_of_people); ?></span></div>
            <div class="receipt-row"><strong>Arrival Date:</strong> <span><?php echo htmlspecialchars($arrival_date); ?></span></div>
            <div class="receipt-row"><strong>Leaving Date:</strong> <span><?php echo htmlspecialchars($leaving_date); ?></span></div>
            <div class="receipt-row"><strong>Status:</strong> <span>Confirmed</span></div>
            <div class="note"><strong>Note:</strong> Thank you for booking with Escape Avenue!</div>
            <form id="cancelForm" action="cancelBooking.php" method="POST">
                <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                <button type="button" onclick="confirmCancellation()">Cancel Booking</button>
            </form>
        </div>
    <?php else: ?>
        <p>No booking found with the provided ID.</p>
    <?php endif; ?>

    <script>
        function confirmCancellation() {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, Cancel my booking!"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("cancelForm").submit();
                }
            });
        }
    </script>
</body>
</html>

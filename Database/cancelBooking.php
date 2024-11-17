<?php
require_once 'BookingDatabase.php';
require_once 'Database.php';

$database = new Database();
$bookingDb = new BookingDatabase($database);

$bookingId = $_POST['booking_id'] ?? null;

if ($bookingId) {
    $result = $bookingDb->cancelBooking($bookingId);
    echo json_encode([
        "success" => $result,
        "message" => $result ? "Booking canceled" : "Cancellation failed"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Booking ID is required."
    ]);
}

$database->close();
?>
